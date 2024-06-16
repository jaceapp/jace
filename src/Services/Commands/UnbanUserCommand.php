<?php

namespace JaceApp\Jace\Services\Commands;

use JaceApp\Jace\Models\JaceBannedUser;
use JaceApp\Jace\Interfaces\CommandInterface;
use App\Models\User;
use Exception;
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

class UnbanUserCommand implements CommandInterface
{
    protected $userProfileRepository;
    protected $userService;
    protected $guestService;
    protected $guestRepository;
    protected $description = 'Unbans a user/guest from the chat';


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
            return $this->unbanUser($targetUser);
        }

        return $this->unbanGuest($targetUser);
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
     * Unban user
     *
     * @param array @targetUser
     * @return array
     **/
    private function unbanUser(array $targetUser): array
    {
        try {
            JaceBannedUser::where('user_id', $targetUser['id'])
                        ->where(function($query) {
                            $query->whereNull('end_date')
                                ->orWhere('end_date', '>=', now())
                                ->where('type', 'timeout');
                        })
                        ->update(['end_date' => now()->subMinutes(1)]);

            // Force user cache refresh / this handles if they refreshed the page
            $this->userProfileRepository->forceFindRefresh($targetUser['id']);
            // Send ban via socket / real time
            $this->sendBanEvent($targetUser['uid']);

            $this->sendMessageEvent($targetUser['username']);

            return [
                'status' => 'success',
                'type' => MessageTypeEnum::COMMAND,
                'message' => 'User was unbanned',
            ];
        } catch (Exception) {
            return [
                'status' => 'error',
                'type' => MessageTypeEnum::COMMAND,
                'message' => 'Error while unbanning user',
            ];
        }
    }

    /**
     * Unban guest
     *
     * @param array $targetUser
     * @return array
     **/
    private function unbanGuest(array $targetUser): array
    {
        try {
            JaceBannedUser::where('guest_id', $targetUser['id'])
                        ->where(function($query) {
                            $query->whereNull('end_date')
                                ->orWhere('end_date', '>=', now())
                                ->where('type', 'timeout');
                        })
                        ->update(['end_date' => now()->subMinutes(1)]);

            // Force user cache refresh / this handles if they refreshed the page
            $this->guestRepository->forceFindByTokenRefresh($targetUser['uid']);
            // Send ban event to socket 
            $this->sendBanEvent($targetUser['uid']);
            // Send message event to socket
            $this->sendMessageEvent($targetUser['username']);

            return [
                'status' => 'success',
                'type' => MessageTypeEnum::COMMAND,
                'message' => 'User was unbanned',
            ];
        } catch (Exception) {
            return [
                'status' => 'error',
                'type' => MessageTypeEnum::COMMAND,
                'message' => 'Error while unbanning user',
            ];
        }
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
            'text' => 'This '.$targetUser.' was unbanned',
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
            'ban_type' => UserStatusEnum::GOOD_STANDING,
        ];
        // Send ban via socket / real time
        event(new ChatRoomEvent('BanUserEvent', $payload));
    }
}
