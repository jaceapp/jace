<?php

namespace JaceApp\Jace\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use JaceApp\Jace\Enums\MessageTypeEnum;

class RenameCommandTest extends TestCase
{
    /**
     * Test rename command success.
     */
    public function testRenameCommandSuccess(): void
    {
        $response = $this->post(route('yace.chat.start-chat'));
        $message = '/rename username';
        $response = $this->post(route('yace.chat.send-message'), ['message' => $message]);
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'type' => MessageTypeEnum::MESSAGE,
        ]);

        // TODO: Check if username was changed
    }
}
