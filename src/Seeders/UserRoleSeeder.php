<?php

namespace JaceApp\Jace\Seeders;

use App\Models\User as ModelsUser;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use JaceApp\Jace\Enums\RoleEnum;

class UserRoleSeeder extends Seeder
{
    /**
     * If the User Model doesn't exist or was moved then this will break. Use this seeder with caution.
     */
    public function run(): void
    {
        $usersModel = config('auth.providers.users.model');
        $users = app($usersModel)::all();
        foreach($users as $user) {
            $user = app($usersModel)::find($user->id);
            $user->assignRole(RoleEnum::NORMAL);
        }
    }
}
