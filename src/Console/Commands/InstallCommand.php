<?php

namespace JaceApp\Jace\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jace:install
                            {--composer=global : Absolute path to the Composer binary which should be used to install packages}';

    /**
     * The console Clean up user related things.
     *
     * @var string
     */
    protected $description = 'Installs JACE into your Laravel application.';

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
        $this->copyConfigs();
        $this->copyMigrations();
        $this->installComposerPackages();
        /* $this->publishSpatie(); */
        $this->copyWebsocket();
    }

    /**
     * Copy over config files
     */
    private function copyConfigs()
    {
        (new Filesystem)->copyDirectory(__DIR__ . '/../../../config', base_path('config'));
        $this->info('Config files published successfully.');
    }

    /**
     * Copy over the migrations
     *
     * @return void
     */
    private function copyMigrations()
    {
        (new Filesystem)->ensureDirectoryExists(base_path('database/migrations'));
        (new Filesystem)->copyDirectory(__DIR__ . '/../../../database/migrations', base_path('database/migrations'));
        $this->info('Migrations published successfully.');
    }

    private function copyWebsocket()
    {
        (new Filesystem)->copyDirectory(__DIR__ . '/../../../websockets', base_path());
    }

    /**
     * Install all needed composer packages
     *
     * @return void
     */
    private function installComposerPackages()
    {
        $this->requireComposerPackages([
            'spatie/laravel-permission:^6.3',
        ]);
    }

    /**
     * Publish Spatie config files + migrations
     *
     * @return void
     */
    /* private function publishSpatie() */
    /* { */
    /*     $this->call('vendor:publish', [ */
    /*         '--provider' => 'Spatie\Permission\PermissionServiceProvider', */
    /*     ]); */
    /* } */

    /**
     * Installs the given Composer Packages into the application.
     * This method is used to require composer packages programmatically.
     * 
     * It is adapted from requireComposerPackages authored by @taylorotwell
     * in the https://github.com/laravel/breeze/ repository available under the MIT license.
     * @param  array  $packages
     * @param  bool  $asDev
     * @return bool
     */
    private function requireComposerPackages(array $packages, $asDev = false)
    {
        $composer = $this->option('composer');

        if ($composer !== 'global') {
            $command = ['php', $composer, 'require'];
        }

        $command = array_merge(
            $command ?? ['composer', 'require'],
            $packages,
            $asDev ? ['--dev'] : [],
        );

        return (new Process($command, base_path(), ['COMPOSER_MEMORY_LIMIT' => '-1']))
            ->setTimeout(null)
            ->run(function ($type, $output) {
                $this->output->write($output);
            }) === 0;
    }
}
