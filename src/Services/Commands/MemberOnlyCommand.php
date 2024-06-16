<?php

namespace JaceApp\Jace\Services\Commands;

use Illuminate\Contracts\Auth\Authenticatable;
use JaceApp\Jace\Enums\ErrorEnums;
use JaceApp\Jace\Enums\MessageTypeEnum;
use JaceApp\Jace\Enums\PermissionEnum;
use JaceApp\Jace\Interfaces\CommandInterface;
use App\Models\User;
use Illuminate\Support\Facades\Redis;
use JaceApp\Jace\Events\ChatRoomEvent;

class MemberOnlyCommand implements CommandInterface
{
    public function handle(array $args, Authenticatable $user): array
    {
        $validated = $this->validate($args, $user);
        if (!empty($validated) > 0) {
            return $validated;
        }

        // TODO This is a temp solution to stop guest mode. When channels come into play, it should use the channel configs
        $cacheKey = 'channel:1:visibility';
        Redis::set($cacheKey, $args[0] === 'on' ? 1 : 0);
        $this->sendMessageEvent($args[0]);

        return [
            'status' => 'success',
            'message' => 'Channel setting has been changed',
        ];
    }

    /**
     * Validate the command input
     *
     * @param array $args
     * @param User|Authenticatable $user
     * @return array
     */
    private function validate(array $args, Authenticatable $user): array
    {
        /* if (!$user->hasPermissionTo(PermissionEnum::MODERATOR_PERMISSIONS['channel-settings'])) { */
        /*     return [ */
        /*         'status' => 'error', */
        /*         'code' => ErrorEnums::PERMISSION_DENIED, */
        /*         'type' => MessageTypeEnum::COMMAND, */
        /*         'message' => 'Do not have permission to edit channel settings', */
        /*     ]; */
        /* } */

        if (empty($args[0])) {
            return [
                'status' => 'error',
                'code' => ErrorEnums::INVALID_ARGUMENTS,
                'type' => MessageTypeEnum::COMMAND,
                'message' => "Missing Parameters",
            ];
        }

        if (!in_array($args[0], ['on', 'off'])) {
            return [
                'status' => 'error',
                'code' => ErrorEnums::INVALID_ARGUMENTS,
                'type' => MessageTypeEnum::COMMAND,
                'message' => 'Invalid Parameters. Should be on/off',
            ];
        }

        return [];
    }

    /**
     * Send Information Message Event
     *
     * @param string $targetUser
     * @return void
     **/
    private function sendMessageEvent(string $status): void
    {
        $payload = [
            'type' => 'information',
            'text' => 'Channel was set to: ' . ($status === 'on' ? 'Members Only' : 'Open to all'),
        ];

        event(new ChatRoomEvent('SendMessageEvent', $payload));
    }
}
