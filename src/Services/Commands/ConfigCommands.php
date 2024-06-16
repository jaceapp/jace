<?php

namespace JaceApp\Jace\Services\Commands;

class ConfigCommands
{
    public const COMMANDS = [
        '/color' => ColorCommand::class,
        '/delete' => DeleteMessageCommand::class,
        '/ban'  => BanUserCommand::class,
        '/unban' => UnbanUserCommand::class,
        '/suspend' => SuspendUserCommand::class,
        '/unsuspend' => UnbanUserCommand::class, // Same as UnbanUserCommand
        '/rename' => RenameCommand::class,
        '/givemeadmin' => GiveMeAdminCommand::class,
        '/memberonly' => MemberOnlyCommand::class,
        // '/mod'
        // '/unmod'
        // '/block'
        // '/unblock'
    ];
}
