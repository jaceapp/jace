<?php

namespace JaceApp\Jace\Enums;

class PermissionEnum
{
    const NORMAL_PERMISSIONS = [
        'block' => 'Block',
        'color' => 'Color',
        'rename' => 'Rename',
        'delete-message' => 'Delete Message',
    ];

    const MODERATOR_PERMISSIONS = [
        'delete-user-message' => 'Delete User Message',
        'rename-user' => 'Rename User',
        'ban-user' => 'Ban User',
        'color-user' => 'Color User',
        'nuke-users' => 'Nuke Users',
        'channel-settings' => 'Channel Settings',
    ];

    const ADMINISTRATOR_PERMISSIONS = [
        'permissions-roles' => 'Permissions & Roles',
        'settings' => 'Settings',
    ];
}
