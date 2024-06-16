<?php

namespace JaceApp\Jace\Tests;

use Carbon\Carbon;
use JaceApp\Jace\Enums\ErrorEnums;
use JaceApp\Jace\Enums\MessageTypeEnum;
use JaceApp\Jace\Enums\UserStatusEnum;
use JaceApp\Jace\Models\JaceBannedUser;
use Illuminate\Support\Str;
use JaceApp\Jace\Enums\RoleEnum;
use JaceApp\Jace\Models\JaceGuest;

class UnbanUserCommandTest extends TestCase
{
    public function testUnbanUserWithoutPermissions()
    {
        $this->startChat(true);
        $message = '/unban @john';
        $response = $this->actingAs($this->testUser)->post(route('yace.chat.send-message'), ['message' => $message]);
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'error',
            'code' => ErrorEnums::PERMISSION_DENIED,
            'type' => MessageTypeEnum::COMMAND,
        ]);
    } 

    public function testUnbanUserSuccess()
    {
        $jaceBannedUser = JaceBannedUser::create([
            'user_id' => 2,
            'type' => UserStatusEnum::BANNED,
            'start_date' => Carbon::now()
        ]);

        $this->startChat(true);
        $message = '/unban @john';
        $this->testUser->removeRole(RoleEnum::NORMAL);
        $this->testUser->assignRole(RoleEnum::MODERATOR);
        $response = $this->actingAs($this->testUser)->post(route('yace.chat.send-message'), ['message' => $message]);
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'type' => MessageTypeEnum::COMMAND,
        ]);

        $exists = JaceBannedUser::where('id', $jaceBannedUser->id)
                    ->whereNull('end_date')
                    ->exists();
        $this->assertFalse($exists);
    }

    public function testUnbanGuestSuccess()
    {
        $jaceGuest = JaceGuest::create([
           'uid' => Str::uuid()->toString(),
           'username' => 'GuestUser',
        ]);
        $jaceBannedUser = JaceBannedUser::create([
            'guest_id' => $jaceGuest->id,
            'type' => UserStatusEnum::BANNED,
            'start_date' => Carbon::now()
        ]);

        $this->startChat(true);
        $message = '/unban @GuestUser';
        $this->testUser->removeRole(RoleEnum::NORMAL);
        $this->testUser->assignRole(RoleEnum::MODERATOR);
        $response = $this->actingAs($this->testUser)->post(route('yace.chat.send-message'), ['message' => $message]);
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'type' => MessageTypeEnum::COMMAND,
        ]);

        $exists = JaceBannedUser::where('id', $jaceBannedUser->id)
                    ->whereNull('end_date')
                    ->exists();
        $this->assertFalse($exists);
    }
}
