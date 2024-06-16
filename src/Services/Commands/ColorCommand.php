<?php

namespace JaceApp\Jace\Services\Commands;

use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use JaceApp\Jace\Enums\ColorEnums;
use JaceApp\Jace\Enums\ErrorEnums;
use JaceApp\Jace\Enums\MessageTypeEnum;
use JaceApp\Jace\Enums\PermissionEnum;
use JaceApp\Jace\Interfaces\CommandInterface;
use JaceApp\Jace\Models\JaceUserProfile;
use JaceApp\Jace\Repositories\JaceUserProfileRepository;
use JaceApp\Jace\Services\GuestService;
use JaceApp\Jace\Services\UserService;

class ColorCommand implements CommandInterface
{
    protected $userProfileRepository;
    protected $userService;
    protected $guestService;
    protected $description = 'Change your own colour or someone elses color';

    public function __construct(JaceUserProfileRepository $userProfileRepository, UserService $userService, GuestService $guestService)
    {
        $this->userProfileRepository = $userProfileRepository;
        $this->userService = $userService;
        $this->guestService = $guestService;
    }

    public function handle(array $args, ?Authenticatable $user): array
    {
        // Start by validating the command input
        $validated = $this->validate($args, $user);
        if (!empty($validated) > 0) {
            return $validated;
        }

        // Let's check if it's an admin command or a user command
        if (isset($args[1]) && ($this->isValidHexcode($args[1]) || $this->isValidColor($args[1]))) {
            return $this->changeOtherUserColor($args, $user);
        }

        // If it's not an admin command, then it's a user command
        return $this->changeUserColor($args, $user);
    }


    /**
     * This command changes your own color /color #000000
     *
     * @param array $args
     * @param Authenticatable|null $user
     * @return array
     */
    private function changeUserColor(array $args, ?Authenticatable $user): array
    {
        // Doing this check because we don't know if a developer will use our login process or their own
        // Our login process will create a user profile for the user
        JaceUserProfile::updateOrCreate(
            ['user_id' => $user->id], // The attributes to find the record
            ['color' => $this->processColour($args[0])] // The values to update or create with
        );

        // Force refresh profile cache
        $this->userProfileRepository->forceFindRefresh($user->id);

        return [
            'status' => 'success',
            'type' => MessageTypeEnum::COMMAND,
            'message' => 'You changed your text color',
        ];
    }

    /**
     * This is an admin command, where you can change another user's color /color @username #000
     *
     * @param array $args
     * @param Authenticatable|null $user
     * @return array
     */
    private function changeOtherUserColor(array $args, ?Authenticatable $user): array
    {
        // Check if user exists in the database
        $user = $this->findByName($args[0]);

        // Return error if user is not found
        if (isset($user['code'])) {
            return $user;
        }

        // Doing this check because we don't know if a developer will use our login process or their own
        // Our login process will create a user profile for the user
        $userProfile = JaceUserProfile::updateOrCreate(
            ['user_id' => $user['id']], // The attributes to find the record
            ['color' => $this->processColour($args[1])] // The values to update or create with
        );

        // Force refresh profile cache
        $this->userProfileRepository->forceFindRefresh($user['id']);

        return [
            'status' => 'success',
            'type' => MessageTypeEnum::COMMAND,
            'message' => 'You changed your text color',
        ];
    }

    /**
     * Validate the command
     *
     * @param array $args
     * @param User|Authenticatable|null $user
     * @return array
     */
    private function validate(array $args, ?Authenticatable $user): array
    {
        if (!isset($args[0])) {
            return [
                'status' => 'error',
                'code' => ErrorEnums::INVALID_ARGUMENTS,
                'type' => MessageTypeEnum::COMMAND,
                'message' => 'Use /color <HEXCODE> to change color',
            ];
        }

        // Changing other peoples text color
        if (substr($args[0], 0, 1) === '@') {
            if (!$user->hasPermissionTo(PermissionEnum::MODERATOR_PERMISSIONS['color-user'])) {
                return [
                    'status' => 'error',
                    'code' => ErrorEnums::PERMISSION_DENIED,
                    'type' => MessageTypeEnum::COMMAND,
                    'message' => 'You do not have permission to change other peoples text color',
                ];
            }


            if (!$this->isValidColor($args[1]) && !$this->isValidHexcode($args[1])) {
                return [
                    'status' => 'error',
                    'code' => ErrorEnums::INVALID_COLOR,
                    'type' => MessageTypeEnum::COMMAND,
                    'message' => 'Invalid color',
                ];
            }

            return [];
        }

        // self change color
        if (!$user->hasPermissionTo(PermissionEnum::NORMAL_PERMISSIONS['color'])) {
            return [
                'status' => 'error',
                'code' => ErrorEnums::PERMISSION_DENIED,
                'type' => MessageTypeEnum::COMMAND,
                'message' => 'You do not have permission to change your color',
            ];
        }


        if (!$this->isValidColor($args[0]) && !$this->isValidHexcode($args[0])) {
            return [
                'status' => 'error',
                'code' => ErrorEnums::INVALID_COLOR,
                'type' => MessageTypeEnum::COMMAND,
                'message' => 'Invalid color',
            ];
        }

        return [];
    }

    /**
     * Check if hex code is valid
     *
     * @param string $hexCode
     * @return boolean
     */
    private function isValidHexcode(string $hexCode): bool
    {
        $hexCode = $this->stripHash($hexCode);
        if (str_starts_with(strtolower($hexCode), '0x')) {
            $hexCode = substr($hexCode, 2);
        }

        return ctype_xdigit($hexCode);
    }

    /**
     * Check if color is valid
     *
     * @param string $color
     * @return boolean
     */
    private function isValidColor(string $color): bool
    {
        // Get color bindings
        if (isset(ColorEnums::COLORS[$color])) {
            return true;
        }

        return false;
    }

    /**
     * Strip the hash from the hex code
     *
     * @param string $hexCode
     * @return string
     */
    private function stripHash(string $hexCode): string
    {
        return str_replace('#', '', $hexCode);
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
     * Process colour (color names should conver to hex code, hex code should remain the same)
     *
     * @param string $color
     * @return string
     */
    private function processColour(string $color): string
    {
        if ($this->isValidHexcode($color)) {
            return $this->stripHash($color);
        }

        return ColorEnums::COLORS[$color];
    }
}
