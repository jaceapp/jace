<?php

namespace JaceApp\Jace\Services\Commands;

use Illuminate\Contracts\Auth\Authenticatable;
use JaceApp\Jace\Enums\ErrorEnums;
use JaceApp\Jace\Enums\MessageTypeEnum;
use JaceApp\Jace\Interfaces\CommandInterface;
use Spatie\Permission\Models\Role;

class GiveMeAdminCommand implements CommandInterface 
{
    /**
     * Handle GivemeAdminCommand
     *
     * @param array $args
     * @param Authenticatable $user
     * @return array
     **/
    public function handle(array $args, Authenticatable $user): array
    {
        $validated = $this->validate($args, $user);
        if (!empty($validated) > 0) {
            return $validated;
        }

        // Need the user model for this not the Authenticatable. (HasRoles lives on the model) 
        $usersModel = config('auth.providers.users.model');
        $user = app($usersModel)::find($user->id);
        $user->assignRole('administrator');

        return [
            'status' => 'success',
            'message' => 'You are admin',
        ];
    }

    private function validate(array $args): array
    {
        if (empty(config('jace.admin_password'))) {
            return [
                'status' => 'error',
                'code' => ErrorEnums::PERMISSION_DENIED,
                'type' => MessageTypeEnum::COMMAND,
                'message' => 'You\'re missing the admin password in your env. Check config/jace.php',
            ];
        }

        if (!isset($args[0])) {
            return [
                'status' => 'error',
                'code' => ErrorEnums::INVALID_ARGUMENTS,
                'type' => MessageTypeEnum::COMMAND,
                'message' => 'Invalid Arguments',
            ];
        }

        if ($this->hasAdministrators()) {
            return [
                'status' => 'error',
                'code' => ErrorEnums::PERMISSION_DENIED,
                'type' => MessageTypeEnum::COMMAND,
                'message' => 'Administrator already set',
            ];
        }

        if (!empty(config('jace.admin_password')) && !empty($args[0]) && config('jace.admin_password') !== $args[0]) {
            return [
                'status' => 'error',
                'code' => ErrorEnums::PERMISSION_DENIED,
                'type' => MessageTypeEnum::COMMAND,
                'message' => 'Incorrect Password',
            ];
        }

        return [];
    }

    /**
     * Check if Administrator is already set
     *
     * @return boolean
     **/
    private function hasAdministrators(): bool
    {
        $role = Role::findByName('administrator');

        if ($role && $role->users()->count() > 0) {
            return true;
        }

        return false;
    }
}
