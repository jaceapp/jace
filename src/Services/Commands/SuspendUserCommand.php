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
use JaceApp\Jace\Repositories\JaceUserProfileRepository;
use JaceApp\Jace\Repositories\JaceGuestRepository;

class SuspendUserCommand implements CommandInterface
{
    protected $userProfileRepository;
    protected $userService;
    protected $guestService;
    protected $guestRepository;
    protected $description = 'Suspends a user/guest from chat';


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

        $sourceUser = $this->userProfileRepository->find($user->id);
        $targetUser = $this->findByName($args[0]);

        if (isset($targetUser['type']) && $targetUser['type'] === 'user') {
            return $this->suspendUser($args, $targetUser, $sourceUser);
        }

        return $this->suspendGuest($args, $targetUser, $sourceUser);
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
     * Looks for the username in members and guests (TODO trait or parent class needed)
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
     * Suspend User /suspend @user<0> <minutes<1>> <reason<2>>
     *
     * @param array $args
     * @param array $targetUser
     * @param array $sourceUser
     * @return array
     */
    private function suspendUser(array $args, array $targetUser, array $sourceUser): array
    {
        // Timeout
        $createUser = [
            'user_id' => $targetUser['id'],
            'type' => UserStatusEnum::TIMEOUT,
            'start_date' => now(),
            'end_date' => now()->addMinutes($args[1]),
            'reason' => $args[2] ?? null,
        ];

        JaceBannedUser::create($createUser);

        // Force user cache refresh / this handles if they refreshed the page
        $this->userProfileRepository->forceFindRefresh($targetUser['id']);

        // Send ban via socket / real time
        $this->sendBanEvent($targetUser['uid']);

        $this->sendMessageEvent($targetUser['username'], $sourceUser['username']);

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
     * @param array $sourceUser
     * @return $array 
     */
    private function suspendGuest(array $args, array $targetUser, array $sourceUser): array
    {
        $createUser = [
            'guest_id' => $targetUser['id'],
            'type' => UserStatusEnum::TIMEOUT,
            'start_date' => now(),
            'end_date' => now()->addMinutes($args[1]),
            'reason' => $args[2] ?? null,
        ];
        JaceBannedUser::create($createUser);

        $this->guestRepository->forceFindByTokenRefresh($targetUser['uid']);

        $this->sendBanEvent($targetUser['uid']);

        $this->sendMessageEvent($targetUser['username'], $sourceUser['username']);

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
     * @param string $sourceUser
     * @return void
     **/
    private function sendMessageEvent(string $targetUser, string $sourceUser): void
    {
        $payload = [
            'type' => 'information',
            'text' => 'This '.$targetUser.' was suspended by '.$sourceUser, //TODO suspend "by" is cool, but too much information
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
            'ban_type' => UserStatusEnum::TIMEOUT,
        ];
        // Send ban via socket / real time
        event(new ChatRoomEvent('BanUserEvent', $payload));
    }
}
