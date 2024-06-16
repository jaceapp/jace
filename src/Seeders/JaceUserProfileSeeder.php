<?php

namespace JaceApp\Jace\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use JaceApp\Jace\Models\JaceUserProfile;
use Illuminate\Support\Str;

class JaceUserProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = config('auth.providers.users.model');
        $users = app($users)::all();
        foreach($users as $user) {
            JaceUserProfile::create([
                'user_id' => $user->id,
                'username' => $user->name,
                'uid' => Str::uuid()->toString(),
                'color' => 'fff',
            ]);
        }
    }
}
