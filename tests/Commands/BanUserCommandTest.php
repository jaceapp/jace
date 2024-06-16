<?php

namespace JaceApp\Jace\Tests;

use JaceApp\Jace\Enums\ErrorEnums;
use JaceApp\Jace\Enums\MessageTypeEnum;
use JaceApp\Jace\Models\JaceGuest;
use JaceApp\Jace\Repositories\JaceUserProfileRepository;
use Illuminate\Support\Str;
use JaceApp\Jace\Enums\RoleEnum;
use JaceApp\Jace\Enums\UserStatusEnum;
use JaceApp\Jace\Repositories\JaceGuestRepository;

class BanUserCommandTest extends TestCase
{
    public function testBanUserWithoutPermission()
    {
        $this->startChat(true);
        $message = '/ban @john';
        $response = $this->actingAs($this->testUser)->post(route('yace.chat.send-message'), ['message' => $message]);
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'error',
            'code' => ErrorEnums::PERMISSION_DENIED,
            'type' => MessageTypeEnum::COMMAND,
        ]);
    }

    public function testBanUserSuccess()
    {
        $this->startChat(true);
        $message = '/ban @john';
        $this->testUser->removeRole(RoleEnum::NORMAL);
        $this->testUser->assignRole(RoleEnum::MODERATOR);
        $response = $this->actingAs($this->testUser)->post(route('yace.chat.send-message'), ['message' => $message]);
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'type' => MessageTypeEnum::COMMAND,
        ]);

        // Check if person was banned
        $profile = app(JaceUserProfileRepository::class)->findByName('john');
        $this->assertEquals($profile['status'], UserStatusEnum::BANNED);
    }

    public function testBanGuestSuccess()
    {
        $jaceGuest = JaceGuest::create([
           'uid' => Str::uuid()->toString(),
           'username' => 'GuestUser',
        ]);
        
        $this->startChat(true);
        $message = '/ban @'.$jaceGuest->username;
        $this->testUser->removeRole(RoleEnum::NORMAL);
        $this->testUser->assignRole(RoleEnum::MODERATOR);
        $response = $this->actingAs($this->testUser)->post(route('yace.chat.send-message'), ['message' => $message]);
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'type' => MessageTypeEnum::COMMAND,
        ]);
        $profile = app(JaceGuestRepository::class)->findByToken($jaceGuest->uid);
        $this->assertEquals($profile['status'], UserStatusEnum::BANNED);
    }
}
