<?php

namespace JaceApp\Jace\Services\Commands;

use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Log;
use JaceApp\Jace\Enums\ErrorEnums;
use JaceApp\Jace\Enums\MessageTypeEnum;
use JaceApp\Jace\Enums\PermissionEnum;
use JaceApp\Jace\Interfaces\CommandInterface;
use JaceApp\Jace\Models\JaceUserProfile;
use JaceApp\Jace\Repositories\JaceUserProfileRepository;

class RenameCommand implements CommandInterface
{
    protected $userProfileRepository;

    public function __construct(JaceUserProfileRepository $userProfileRepository)
    {
        $this->userProfileRepository = $userProfileRepository;
    }

    public function handle(array $args, Authenticatable $user): array
    {
        $validated = $this->validate($args, $user);
        if (!empty($validated) > 0) {
            return $validated;
        }

        $sourceUser = $this->userProfileRepository->find($user->id);

        return $this->rename($args, $sourceUser);
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
        if (!$user->hasPermissionTo(PermissionEnum::NORMAL_PERMISSIONS['rename'])) {
            return [
                'status' => 'error',
                'code' => ErrorEnums::PERMISSION_DENIED,
                'message' => 'You do not have permissions to rename',
            ];
        }

        if (empty($args[0])) {
            return [
                'status' => 'error',
                'code' => ErrorEnums::INVALID_ARGUMENTS,
                'message' => 'Invalid arguments',
            ];
        }

        return [];
    }

    /**
     * Rename current users name
     *
     * @param array $args
     * @param array $user
     * @return array
     **/
    private function rename(array $args, array $user): array 
    {
        if ($this->userProfileRepository->doesNameExist($args[0])) {
            return [
                'status' => 'error',
                'code' => ErrorEnums::USER_FOUND, 
                'type' => MessageTypeEnum::COMMAND,
                'message' => 'Username already taken',
            ];
        }

        $userProfile = JaceUserProfile::find($user['id']);
        if (!$userProfile) {
            Log::error('RenameCommand. JaceUserProfile not found: ' . $user['id']);
            return [
                'status' => 'error',
                'code' => ErrorEnums::USER_NOT_FOUND, 
                'type' => MessageTypeEnum::COMMAND,
                'message' => 'User not found',
            ];
        }

        $userProfile->username = $args[0];
        $userProfile->save();

        $this->userProfileRepository->forceFindRefresh($user['id']);

        return [
            'status' => 'success',
            'type' => MessageTypeEnum::COMMAND,
            'message' => 'Rename successful',
        ];
    }
}
