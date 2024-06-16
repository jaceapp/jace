<?php

namespace JaceApp\Jace\Services\Commands;

use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use JaceApp\Jace\Enums\ErrorEnums;
use JaceApp\Jace\Enums\MessageTypeEnum;
use JaceApp\Jace\Enums\PermissionEnum;
use JaceApp\Jace\Events\ChatRoomEvent;
use JaceApp\Jace\Interfaces\CommandInterface;
use JaceApp\Jace\Models\JaceChatHistory;
use JaceApp\Jace\Repositories\JaceUserProfileRepository;
use JaceApp\Jace\Services\GuestService;
use JaceApp\Jace\Services\UserService;

class DeleteMessageCommand implements CommandInterface
{

    protected $userProfileRepository;
    protected $userService;
    protected $guestService;

    const DEFAULT_MESSAGE_COUNT = 1;

    public function __construct(JaceUserProfileRepository $userProfileRepository, UserService $userService, GuestService $guestService)
    {
        $this->userProfileRepository = $userProfileRepository;
        $this->userService = $userService;
        $this->guestService = $guestService;
    }

    public function handle(array $args, Authenticatable $user): array
    {
        $validated = $this->validate($args, $user);
        if (!empty($validated) > 0) {
            return $validated;
        }

        // Check if user exists in the database
        $user = $this->findByName($args[0]);

        // Return error if user is not found
        if (isset($user['code'])) {
            return $user;
        }
        
        $messages = JaceChatHistory::select('id')
            ->when($user['type'] === 'user', function ($query) use ($user) {
                $query->where('user_id', $user['id']);
            }, function ($query) use ($user) {
                $query->where('guest_id', $user['id']);
            })
            ->orderBy('id', 'desc')
            ->limit($args[1] ?? self::DEFAULT_MESSAGE_COUNT);

        // Grab only the message Ids
        $messageIds = $messages->pluck('id')
            ->toArray();

        $messages->delete();

        event(new ChatRoomEvent('DeleteMessagesEvent', ['messages' => $messageIds]));

        return [
            'status' => 'success',
            'type' => MessageTypeEnum::COMMAND,
            'message' => 'Message deleted',
        ];
    }

    /**
     * Go through validation checks, and return an array of errors if any
     *
     * @param array $args
     * @param User|Authenticatable $user
     * @return array
     */
    private function validate(array $args, Authenticatable $user): array
    {
        if (!$user->hasPermissionTo(PermissionEnum::MODERATOR_PERMISSIONS['delete-user-message'])) {
            return [
                'status' => 'error',
                'code' => ErrorEnums::PERMISSION_DENIED,
                'type' => MessageTypeEnum::COMMAND,
                'message' => 'You do not have permission to delete user messages',
            ];
        }

        if (count($args) < 1) {
            return [
                'status' => 'error',
                'code' => ErrorEnums::INVALID_ARGUMENTS,
                'type' => MessageTypeEnum::COMMAND,
                'message' => 'You must specify a user to delete messages from',
            ];
        }

        if (isset($args[1]) && filter_var($args[1], FILTER_VALIDATE_INT) === false) {
            return [
                'status' => 'error',
                'code' => ErrorEnums::INVALID_ARGUMENTS,
                'type' => MessageTypeEnum::COMMAND,
                'message' => 'Invalid arguments',
            ];
        }

        return [];
    }

    /**
     * Looks for the username in members and guests
     *
     * @param string $username
     * @return array
     */
    private function findByName(string $username): array
    {
        $response = $this->userService->findByName($username);

        // if user is not found, check if it's a guest
        if (isset($response['code'])) {
            $response = $this->guestService->findByName($username);
        }

        return $response;
    }
}
