<?php

namespace JaceApp\Jace\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use JaceApp\Jace\Enums\PermissionEnum;
use JaceApp\Jace\Enums\RoleEnum;

class RolePermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Normal
        $normal = Role::findByName(RoleEnum::NORMAL);
        foreach (PermissionEnum::NORMAL_PERMISSIONS as $permission) {
            $normal->givePermissionTo($permission);
        }

        // Moderator
        $moderator = Role::findByName(RoleEnum::MODERATOR);
        // Give normalPermissions to moderator
        foreach (PermissionEnum::NORMAL_PERMISSIONS as $permission) {
            $moderator->givePermissionTo($permission);
        }
        // Give moderatorPermissions to moderator
        foreach (PermissionEnum::MODERATOR_PERMISSIONS as $permission) {
            $moderator->givePermissionTo($permission);
        }


        // Administrator
        $administrator = Role::findByName('administrator');
        // Give normalPermissions to moderator
        foreach (PermissionEnum::NORMAL_PERMISSIONS as $permission) {
            $administrator->givePermissionTo($permission);
        }
        // Give moderatorPermissions to moderator
        foreach (PermissionEnum::MODERATOR_PERMISSIONS as $permission) {
            $administrator->givePermissionTo($permission);
        }
        // Give administratorPermissions to administrator
        foreach (PermissionEnum::ADMINISTRATOR_PERMISSIONS as $permission) {
            $administrator->givePermissionTo($permission);
        }
    }
}
