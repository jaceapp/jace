<?php

namespace JaceApp\Jace\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use JaceApp\Jace\Requests\MessageRequest;
use JaceApp\Jace\Services\ChatService;
use JaceApp\Jace\Services\CommandService;
use JaceApp\Jace\Services\EmoteService;
use JaceApp\Jace\Services\GuestService;
use JaceApp\Jace\Services\MessageParsingService;
use JaceApp\Jace\Services\UserService;

class ChatController extends BaseController
{
    private ChatService $chatService;

    const GUEST_COOKIE_EXPIRE = 60 * 24;
    const EMOJI_SELECT_QUERY = ['shortcode', 'image_url'];

    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }
    
    /**
     * Start Chat / Conversations
     *
     * @param UserService $userService
     * @param GuestService $guestService
     * @return JsonResponse
     */
    public function startChat(UserService $userService, GuestService $guestService, EmoteService $emoteService)
    {
        /** 
         * Initilize Guest Chat
         */
        if (!Auth::check()) {
            $guestInformation = Arr::only($guestService->bootGuest(), ['uid', 'username', 'status', 'type', 'cookie']);
            if (isset($guestInformation['cookie'])) {
                $cookie = $guestInformation['cookie'];
                unset($guestInformation['cookie']);
                return response()->json([
                    'status' => 'success',
                    'self' => $guestInformation,
                    'emojis' => $emoteService->all(self::EMOJI_SELECT_QUERY),
                ])->cookie($cookie);
            }
            return response()->json([
                'status' => 'success',
                'self' => $guestInformation,
                'emojis' => $emoteService->all(self::EMOJI_SELECT_QUERY),
            ]);
        }

        $userService->bootUser(Auth::user()->id);

        return response()->json([ 
            'status' => 'success',
            'self' => $userService->getUserInformation(Auth::user()->id, ['uid', 'username', 'color', 'status', 'type']), 
            'emojis' => $emoteService->all(self::EMOJI_SELECT_QUERY),
        ]);
    }

    /**
     * Send Message
     *
     * @param MessageRequest $request
     * @param CommandService $commandService
     * @param GuestService $guestService
     * @return JsonResponse
     */
    public function sendMessage(MessageRequest $request, CommandService $commandService, GuestService $guestService, MessageParsingService $messageParsingService): JsonResponse
    {

        // Send Message
        $fields = $request->validated();

        // For guest accounts
        if (!Auth::check()) {
            $guestInformation = $guestService->bootGuest();
            $response = $this->chatService->processMessage(null, $guestInformation['uid'], $fields['message'], $commandService, $messageParsingService);

            return response()->json($response);
        }

        // For logged in accounts
        $response = $this->chatService->processMessage(Auth::user(), 0, $fields['message'], $commandService, $messageParsingService);

        return response()->json($response);
    }
}
