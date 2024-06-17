<?php

namespace JaceApp\Jace\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use JaceApp\Jace\Seeders\PermissionSeeder;
use JaceApp\Jace\Seeders\RolePermissionsSeeder;
use JaceApp\Jace\Seeders\RoleSeeder;
use JaceApp\Jace\Seeders\UserRoleSeeder;
use Illuminate\Support\Facades\Artisan;
use JaceApp\Jace\Seeders\JaceUserProfileSeeder;

class RunSeederCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jace:seed';

    /**
     * The console Clean up user related things.
     *
     * @var string
     */
    protected $description = 'Runs JACE Seeders.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Running PermissionSeeder
        Artisan::call('db:seed', ['--class' => PermissionSeeder::class]);
        $this->info('PermissionSeeder executed successfully.');

        // Running RoleSeeder
        Artisan::call('db:seed', ['--class' => RoleSeeder::class]);
        $this->info('RoleSeeder executed successfully.');

        // Running RolePermissionsSeeder
        Artisan::call('db:seed', ['--class' => RolePermissionsSeeder::class]);
        $this->info('RolePermissionsSeeder executed successfully.');

        // Running UserRoleSeeder
        Artisan::call('db:seed', ['--class' => UserRoleSeeder::class]);
        $this->info('UserRoleSeeder executed successfully.');

        // App should work without this
        /* Artisan::call('db:seed', ['--class' => JaceUserProfileSeeder::class]); */
        /* $this->info('JaceUserProfileSeeder executed successfully.'); */
    }
}
