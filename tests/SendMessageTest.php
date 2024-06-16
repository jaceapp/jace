<?php

namespace JaceApp\Jace\Tests;

use JaceApp\Jace\Enums\MessageTypeEnum;

class SendMessageTest extends TestCase
{
    public function testRegularGuestMessage()
    {
        $response = $this->post(route('yace.chat.start-chat'));
        $response = $this->post(route('yace.chat.send-message'), ['message' => 'This is a test message']);
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'type' => MessageTypeEnum::MESSAGE,
        ]);
    }

    public function testRegularUserMessage()
    {
        $this->startChat(true);
        $response = $this->actingAs($this->testUser)->post(route('yace.chat.send-message'), ['message' => 'This is a test message']);
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'type' => MessageTypeEnum::MESSAGE,
        ]);
    }
}
