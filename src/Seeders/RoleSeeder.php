<?php

namespace JaceApp\Jace\Seeders;

use Illuminate\Database\Seeder;
use JaceApp\Jace\Enums\RoleEnum;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            RoleEnum::NORMAL,
            RoleEnum::MODERATOR,
            RoleEnum::ADMINISTRATOR,
        ];

        $guardName = config('jace.api') ? 'api' : 'web';

        foreach ($roles as $role) {
            Role::create(['name' => $role, 'guard_name' => $guardName]);
        }
    }
}
