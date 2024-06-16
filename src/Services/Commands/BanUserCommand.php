<?php

namespace JaceApp\Jace\Services\Commands;

use JaceApp\Jace\Models\JaceBannedUser;
use JaceApp\Jace\Interfaces\CommandInterface;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use JaceApp\Jace\Enums\ErrorEnums;
use JaceApp\Jace\Enums\MessageTypeEnum;
use JaceApp\Jace\Enums\PermissionEnum;
use JaceApp\Jace\Enums\UserStatusEnum;
use JaceApp\Jace\Services\UserService;
use JaceApp\Jace\Services\GuestService;
use JaceApp\Jace\Events\ChatRoomEvent;
use JaceApp\Jace\Repositories\JaceGuestRepository;
use JaceApp\Jace\Repositories\JaceUserProfileRepository;

class BanUserCommand implements CommandInterface
{
    protected $userProfileRepository;
    protected $userService;
    protected $guestService;
    protected $guestRepository;
    protected $description = 'Bans a user/guest permanantely from the chat';


    public function __construct(UserService $userService, GuestService $guestService, JaceUserProfileRepository $userRepository, JaceGuestRepository $guestRepository)
    {
        $this->userService = $userService;
        $this->userProfileRepository = $userRepository;
        $this->guestService = $guestService;
        $this->guestRepository = $guestRepository;
    }

    public function handle(array $args, Authenticatable $user): array
    {
        $validated = $this->validate($args, $user);
        if (!empty($validated) > 0) {
            return $validated;
        }

        $targetUser = $this->findByName($args[0]);

        if (isset($targetUser['type']) && $targetUser['type'] === 'user') {
            return $this->banUser($args, $targetUser);
        }

        return $this->banGuest($args, $targetUser);
    }

    /**
     * Validate the command input
     *
     * @param array $args
     * @param User|Authenticatable $user
     * @return array
     */
    private function validate(array $args, Authenticatable $user): array
    {
        if (!$user->hasPermissionTo(PermissionEnum::MODERATOR_PERMISSIONS['ban-user'])) {
            return [
                'status' => 'error',
                'code' => ErrorEnums::PERMISSION_DENIED,
                'type' => MessageTypeEnum::COMMAND,
                'message' => 'You do not have permission to ban someone',
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

    /**
     * Ban User /ban @user<0> <reason<0>> 
     *
     * @param array $args
     * @param array $targetUser
     * @return array
     */
    private function banUser(array $args, array $targetUser): array
    {
        // Permanent
        $createUser = [
            'user_id' => $targetUser['id'],
            'type' => UserStatusEnum::BANNED,
            'start_date' => now(),
            'end_date' => null,
            'reason' => $args[1] ?? null,
        ];
        JaceBannedUser::create($createUser);

        $this->sendBanEvent($targetUser['uid']);

        $this->sendMessageEvent($targetUser['username']);
        // Force user cache refresh / this handles if they refreshed the page
        $this->userProfileRepository->forceFindRefresh($targetUser['id']);

        return [
            'status' => 'success',
            'type' => MessageTypeEnum::COMMAND,
            'message' => 'User banned',
        ];
    }

    /**
     * Bans guest from the chat
     *
     * @param array $args
     * @param array $targetUser
     * @return $array 
     */
    private function banGuest(array $args, array $targetUser): array
    {
        $createUser = [
            'guest_id' => $targetUser['id'],
            'type' => UserStatusEnum::BANNED,
            'start_date' => now(),
            'end_date' => null,
            'reason' => $args[1] ?? null,
        ];
        JaceBannedUser::create($createUser);

        $this->sendBanEvent($targetUser['uid']);

        $this->sendMessageEvent($targetUser['username']);

        $this->guestRepository->forceFindByTokenRefresh($targetUser['uid']);

        return [
            'status' => 'success',
            'type' => MessageTypeEnum::COMMAND,
            'message' => 'User banned',
        ];
    }

    /**
     * Send Information Message Event
     *
     * @param string $targetUser
     * @return void
     **/
    private function sendMessageEvent(string $targetUser): void
    {
        $payload = [
            'type' => 'information',
            'text' => 'This '.$targetUser.' was banned.',
        ];

        event(new ChatRoomEvent('SendMessageEvent', $payload));
    }

    /**
     * Send Ban Event
     *
     * @param string $uid
     * @return void
     **/
    private function sendBanEvent(string $uid): void
    {
        $payload = [
            'uid' => $uid,
            'ban_type' => UserStatusEnum::BANNED,
        ];
        // Send ban via socket / real time
        event(new ChatRoomEvent('BanUserEvent', $payload));
    }
}
