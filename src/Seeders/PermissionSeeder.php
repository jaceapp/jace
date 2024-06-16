<?php

namespace JaceApp\Jace\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use JaceApp\Jace\Enums\PermissionEnum;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Loop through the permissions and create them
        $permissions = [];
        foreach (PermissionEnum::NORMAL_PERMISSIONS as $permission) {
            array_push($permissions, $permission);
        }
        foreach (PermissionEnum::MODERATOR_PERMISSIONS as $permission) {
            array_push($permissions, $permission);
        }
        foreach (PermissionEnum::ADMINISTRATOR_PERMISSIONS as $permission) {
            array_push($permissions, $permission);
        }

        $guardName = config('jace.api') ? 'api' : 'web';

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission, 'guard_name' => $guardName]);
        }
    }
}
