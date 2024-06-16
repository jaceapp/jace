<?php

namespace JaceApp\Jace\Tests;

use JaceApp\Jace\Enums\ErrorEnums;
use JaceApp\Jace\Enums\MessageTypeEnum;
use JaceApp\Jace\Enums\RoleEnum;

class ColorCommandTest extends TestCase
{

    public function setUp(): void
    {
        //
        parent::setUp();

        /* $this->testUser->assignRole(RoleEnum::NORMAL); */
    }
    public function testChangeSelfColorSuccess()
    {
        $this->startChat(true);
        $message = '/color #000';
        $response = $this->actingAs($this->testUser)->post(route('yace.chat.send-message'), ['message' => $message]);
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'type' => MessageTypeEnum::COMMAND,
        ]);
    }

    public function testChangeSelfColorWithoutPermission()
    {
        $this->startChat(true);
        $message = '/color #000';
        $this->testUser->removeRole(RoleEnum::NORMAL);
        $response = $this->actingAs($this->testUser)->post(route('yace.chat.send-message'), ['message' => $message]);
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'error',
            'code' => ErrorEnums::PERMISSION_DENIED,
            'type' => MessageTypeEnum::COMMAND,
        ]);
    }

    public function testChangeSelfColorIncorrectHexcode()
    {
        $this->startChat(true);
        $message = '/color ###';
        $response = $this->actingAs($this->testUser)->post(route('yace.chat.send-message'), ['message' => $message]);
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'error',
            'code' => ErrorEnums::INVALID_COLOR,
            'type' => MessageTypeEnum::COMMAND,
        ]);
    }

    public function testChangeOtherSuccess()
    {
        $this->startChat(true);
        $message = '/color @john #000';
        $this->testUser->removeRole(RoleEnum::NORMAL);
        $this->testUser->assignRole(RoleEnum::MODERATOR);
        $response = $this->actingAs($this->testUser)->post(route('yace.chat.send-message'), ['message' => $message]);
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'type' => MessageTypeEnum::COMMAND,
        ]);
    }

    public function testChangeOtherWithoutPermission()
    {
        $this->startChat(true);
        $message = '/color @john #000';
        $response = $this->actingAs($this->testUser)->post(route('yace.chat.send-message'), ['message' => $message]);
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'error',
            'code' => ErrorEnums::PERMISSION_DENIED,
            'type' => MessageTypeEnum::COMMAND,
        ]);
    }

    public function testChangeOtherInvalidHexcode()
    {
        $this->startChat(true);
        $message = '/color @john ##';
        $this->testUser->removeRole(RoleEnum::NORMAL);
        $this->testUser->assignRole(RoleEnum::MODERATOR);
        $response = $this->actingAs($this->testUser)->post(route('yace.chat.send-message'), ['message' => $message]);
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'error',
            'code' => ErrorEnums::INVALID_COLOR,
            'type' => MessageTypeEnum::COMMAND,
        ]);
    }
}
