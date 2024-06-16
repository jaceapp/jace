<?php

namespace JaceApp\Jace\Services;

use Illuminate\Contracts\Auth\Authenticatable;
use JaceApp\Jace\Services\Commands\ConfigCommands;

class CommandService
{
    private $commands = [];

    public function __construct()
    {
        $this->registerComponents();
    }

    /**
     * Checks if a message is a command
     *
     * @param string $message
     * @return boolean
     */
    public function isCommand(string $message): bool
    {
        // Check if the message starts with "/"
        if (substr($message, 0, 1) !== '/') {
            return false;
        }

        // Extract the first word from the message
        $parts = explode(' ', $message);
        $command = $parts[0];

        // Check if the extracted command is in the array of registered commands
        if (!array_key_exists($command, ConfigCommands::COMMANDS)) {
            return false;
        }

        return true;
    }


    /**
     * Run the command that was sent
     * @param Authenticatable $user
     * @param string $message
     * @return array
     */
    public function processCommand(string $message, Authenticatable $user): array
    {
        $command = $this->getCommand($message);
        $args = $this->getArgs($message);

        if (isset($this->commands[$command])) {
            return $this->commands[$command]->handle($args, $user);
        }
    }

    /**
     * Register Commands in a global variable
     *
     * @return void
     */
    private function registerComponents(): void
    {
        foreach (ConfigCommands::COMMANDS as $command => $class) {
            $this->commands[$command] = app($class);
        }
    }

    /**
     * Get the command from the message
     *
     * @param string $message
     * @return string
     */
    private function getCommand(string $message): string
    {
        return explode(' ', $message)[0];
    }

    /**
     * Get the arguements from the message
     *
     * @param string $message
     * @return array
     */
    private function getArgs(string $message): array
    {
        $parts = explode(' ', $message);
        array_shift($parts);

        return $parts;
    }
}
