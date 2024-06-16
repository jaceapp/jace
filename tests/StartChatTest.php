<?php

namespace JaceApp\Jace\Tests;

use JaceApp\Jace\Enums\UserStatusEnum;

class StartChatTest extends TestCase
{
    public function testStartChatGuest()
    {
        $response = $this->post(route('yace.chat.start-chat'));
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'self' => [
                'status' => UserStatusEnum::GOOD_STANDING,
                'type' => 'guest',
            ],
        ]);
    }

    public function testStartChatUser()
    {
        $response = $this->actingAs($this->testUser)->post(route('yace.chat.start-chat'));
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'self' => [
                'status' => UserStatusEnum::GOOD_STANDING,
                'type' => 'user',
            ],
        ]);
    }
}
