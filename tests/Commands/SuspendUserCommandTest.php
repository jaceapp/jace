<?php

namespace JaceApp\Jace\Tests;

use JaceApp\Jace\Enums\ErrorEnums;
use JaceApp\Jace\Enums\MessageTypeEnum;
use JaceApp\Jace\Repositories\JaceUserProfileRepository;
use JaceApp\Jace\Enums\UserStatusEnum;
use JaceApp\Jace\Models\JaceGuest;
use Illuminate\Support\Str;
use JaceApp\Jace\Enums\RoleEnum;
use JaceApp\Jace\Repositories\JaceGuestRepository;

class SuspendUserCommandTest extends TestCase
{
    // TODO: Also test for invalid commands
   public function testSuspendUserWithoutPermission()
   {
       $this->startChat(true);
       $message = '/suspend @john 5';
       $response = $this->actingAs($this->testUser)->post(route('yace.chat.send-message'), ['message' => $message]);
       $response->assertStatus(200);
       $response->assertJson([
           'status' => 'error',
           'code' => ErrorEnums::PERMISSION_DENIED,
           'type' => MessageTypeEnum::COMMAND,
       ]);
   }

   public function testSuspendUserSuccess()
   {
       $this->startChat(true);
       $message = '/suspend @john 5';
       $this->testUser->removeRole(RoleEnum::NORMAL);
       $this->testUser->assignRole(RoleEnum::MODERATOR);
       $response = $this->actingAs($this->testUser)->post(route('yace.chat.send-message'), ['message' => $message]);
       $response->assertStatus(200);
       $response->assertJson([
           'status' => 'success',
           'type' => MessageTypeEnum::COMMAND,
       ]);
       $profile = app(JaceUserProfileRepository::class)->findByName('john');
       $this->assertEquals($profile['status'], UserStatusEnum::TIMEOUT);
   }

    public function testBanGuestSuccess()
    {
        $jaceGuest = JaceGuest::create([
            'uid' => Str::uuid()->toString(),
            'username' => 'GuestUser',
        ]);

        $this->startChat(true);
        $message = '/suspend @'.$jaceGuest->username.' 5';
        $this->testUser->removeRole(RoleEnum::NORMAL);
        $this->testUser->assignRole(RoleEnum::MODERATOR);
        $response = $this->actingAs($this->testUser)->post(route('yace.chat.send-message'), ['message' => $message]);
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'type' => MessageTypeEnum::COMMAND,
        ]);
        $profile = app(JaceGuestRepository::class)->findByToken($jaceGuest->uid);
        $this->assertEquals($profile['status'], UserStatusEnum::TIMEOUT);
    }
}
