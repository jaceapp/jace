<?php

namespace JaceApp\Jace\Services;

use JaceApp\Jace\Requests\LogMessageRequest;
use JaceApp\Jace\Events\LogUserMessageEvent;
use JaceApp\Jace\Events\ChatRoomEvent;
use Illuminate\Contracts\Auth\Authenticatable;
use JaceApp\Jace\Repositories\JaceUserProfileRepository;
use Illuminate\Support\Str;
use JaceApp\Jace\Enums\MessageTypeEnum;
use JaceApp\Jace\Repositories\JaceGuestRepository;

class ChatService
{

    protected $userProfileRepository;
    protected $guestRepository;

    public function __construct(JaceUserProfileRepository $userProfileRepository, JaceGuestRepository $guestRepository)
    {
        $this->guestRepository = $guestRepository;
        $this->userProfileRepository = $userProfileRepository;
    }

    /**
     * Process Message function (maybe this should go into a MessageService)
     *
     * @param Authenticatable $user
     * @param string $guestUid
     * @param string $username
     * @param string $message
     * @param CommandService $commandService
     * @return array
     */
    public function processMessage(?Authenticatable $user, string $guestUid = '', string $message, CommandService $commandService, MessageParsingService $messageParsingService): array
    {
        if ($commandService->isCommand($message) && !is_null($user)) {
            return $commandService->processCommand($message, $user);
        }

        // Default values
        $color = '545454';
        $uid = Str::uuid()->toString();
        
        $messageInformation = $messageParsingService->handle($message); // TODO this might get deprecated. Going to give markdown a try

        // Guest sending is a bit special
        if (is_null($user)) {
            $profile = $this->guestRepository->findByToken($guestUid);
            $this->logMessage('message', $uid, 0, $profile['id'], $messageInformation['message'], $messageInformation['blocks']);

            return $this->sendMessage($uid, $profile['username'], $messageInformation['message'], $messageInformation['blocks'], $color);
        }

        $profile = $this->userProfileRepository->find($user->id);

        $this->logMessage('message', $uid, $user->id, 0, $messageInformation['message'], $messageInformation['blocks']);
        return $this->sendMessage($uid, $profile['username'], $messageInformation['message'], $messageInformation['blocks'], $profile['color']);
    }

    /**
     * Queue message to chat (Maybe this should go into a Message Service.)
     *
     * @param string $username
     * @param string $message
     * @param string $color
     * @return array
     */
    private function sendMessage(string $uid, string $username, string $message, array $blocks, string $color): array
    {
        $payload = [
            'type' => 'message',
            'client_msg_id' => $uid,
            'username' => $username,
            'text' => $message,
            'block' => $blocks,
            'color' => $color,
        ];

        event(new ChatRoomEvent('SendMessageEvent', $payload));

        return [
            'status' => 'success',
            'type' => MessageTypeEnum::MESSAGE,
        ];
    }

    /**
     * Queue logging the message. (Used for moderation, and chat history etc)
     *
     * @param array $profile
     * @param string $message
     * @return array
     */
    private function logMessage(string $type, string $uid, int $userId, int $guestId, string $message, array $blocks): array
    {
        $fields = new LogMessageRequest([
            'type' => $type,
            'uid' => $uid,
            'userId' => $userId,
            'guestId' => $guestId,
            'message' => $message,
            'blocks' => json_encode($blocks),
        ]);
        event(new LogUserMessageEvent($fields));

        return [
            'status' => 'success',
            'type' => MessageTypeEnum::MESSAGE,
        ];
    }
}
