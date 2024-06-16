<?php

namespace JaceApp\Jace\Tests;

use JaceApp\Jace\Enums\ErrorEnums;
use JaceApp\Jace\Enums\MessageTypeEnum;
use JaceApp\Jace\Enums\RoleEnum;
use JaceApp\Jace\Models\JaceChatHistory;

class DeleteMessageCommandTest extends TestCase
{
    public function testDeleteOtherMessageNoPermission()
    {
        $this->startChat(true);
        $message = '/delete @john 5';
        $response = $this->actingAs($this->testUser)->post(route('yace.chat.send-message'), ['message' => $message]);
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'error',
            'code' => ErrorEnums::PERMISSION_DENIED,
            'type' => MessageTypeEnum::COMMAND,
        ]);
    }

    public function testDeleteOtherMessageSuccess()
    {
        // Create dummy data
        $chatMessage = JaceChatHistory::create([
            'user_id' => 2,
            'type' => 'message',
            'message' => 'This is a cool message',
        ]);
        $exists = JaceChatHistory::where('id', $chatMessage->id)->exists();
        $this->assertTrue($exists);

        // Start Moderation Test
        $this->startChat(true);
        $message = '/delete @john 1';
        $this->testUser->removeRole(RoleEnum::NORMAL);
        $this->testUser->assignRole(RoleEnum::MODERATOR);
        $response = $this->actingAs($this->testUser)->post(route('yace.chat.send-message'), ['message' => $message]);
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'type' => MessageTypeEnum::COMMAND,
        ]);

        // Check to see if it deleted the record
        $exists = JaceChatHistory::where('id', $chatMessage->id)->exists();
        $this->assertFalse($exists);
    }

}
