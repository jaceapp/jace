<?php

namespace JaceApp\Jace\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * TODO: Not sure if this will be useful for people, I don't want to publish it based on yace:install, since it might overwrite their existing seeders and that would be sadge
 * Gotta figure something out
 **/
class JaceDatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $users = config('auth.providers.users.model');
        $users::factory()->create([
            'name' => 'roadrunner',
            'email' => 'roadruner@example.com',
        ]);

        // Run all seeders
        $this->call(PermissionSeeder::class);
        $this->call(RoleSeeder::class);
        $this->call(RolePermissionsSeeder::class);
        $this->call(UserRoleSeeder::class);
    }
}
