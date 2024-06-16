<?php

namespace JaceApp\Jace\Tests;

use JaceApp\Jace\Seeders\PermissionSeeder;
use JaceApp\Jace\Seeders\RolePermissionsSeeder;
use JaceApp\Jace\Seeders\RoleSeeder;
use JaceApp\Jace\Seeders\UserRoleSeeder;
use Exception;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as Orchestra;
use JaceApp\Jace\Tests\TestModels\User;
use JaceApp\Jace\JaceServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use JaceApp\Jace\Models\JaceUserProfile;
use JaceApp\Jace\Seeders\JaceUserProfileSeeder;
use Illuminate\Support\Str;

class TestCase extends Orchestra 
{
    protected $testUser;

    public function setUp(): void
    {
        parent::setUp();

        try {
            $this->runMigrations();
            $this->seedData();
        } catch (\Exception $e) {
            Log::info('setUp: '.$e->getMessage());
        }
    }

    public function runMigrations()
    {

        /* $this->loadLaravelMigrations(); */
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations/');
        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->string('password');
                $table->rememberToken();
                $table->timestamps();
            });
        }
    }

    public function seedData()
    {
        $user = User::find(1);
        if ($user === null) {
            $user = User::create([
                'name' => 'roadrunner',
                'email' => 'roadrunner@example.org',
                'password' => Hash::make('password'),
            ]);

            JaceUserProfile::create([
                'user_id' => $user->id,
                'username' => 'roadrunner',
            ]);

            $user = User::create([
                'name' => 'john',
                'email' => 'john@example.org',
                'password' => Hash::make('password'),
            ]);

            JaceUserProfile::create([
                'user_id' => $user->id,
                'username' => 'john',
            ]);
        }

        try {
            $this->seed(PermissionSeeder::class);
            $this->seed(RoleSeeder::class);
            $this->seed(RolePermissionsSeeder::class);
            $this->seed(UserRoleSeeder::class);
            $this->seed(JaceUserProfileSeeder::class);
        } catch (Exception $e) {
            Log::info('seedData: '.$e->getMessage());
        }
        $this->testUser = $user;
    }

    /**
     * Start Chat
     *
     * @param bool $isUser
     * @return void
     **/
    public function startChat(bool $isUser = false) {
        if ($isUser) {
            return $this->actingAs($this->testUser)->post(route('yace.chat.start-chat'));
        }

        $this->post(route('yace.chat.start-chat'));
    }

    protected function getPackageProviders($app)
    {
        return [
            JaceServiceProvider::class
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
        $app['config']->set('jace.api', false);
        $app['config']->set('jace.cache.users_profiles', 1);
        // Use test User model for users provider
        $app['config']->set('auth.providers.users.model', User::class);
    }

    public function tearDown(): void
    {
        // Perform database cleanup
        /* Schema::drop('users'); */
        // Call parent tearDown
        parent::tearDown();
    }
}
