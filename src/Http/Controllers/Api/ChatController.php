<?php

namespace JaceApp\Jace\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use JaceApp\Jace\Requests\MessageRequest;
use JaceApp\Jace\Services\ChatService;
use JaceApp\Jace\Services\CommandService;
use JaceApp\Jace\Services\GuestService;
use JaceApp\Jace\Services\UserService;

class ChatController extends BaseController
{
    private ChatService $chatService;

    const GUEST_COOKIE_EXPIRE = 60 * 24;

    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }
    
    /**
     * Start Chat
     *
     * @param UserService $userService
     * @param GuestService $guestService
     * @return JsonResponse
     */
    public function startChat(UserService $userService, GuestService $guestService)
    {
        /** 
         * Initilize Guest Chat
         */
        if (!Auth::guard('api')->check()) {
            $guestInformation = Arr::only($guestService->bootGuest(), ['uid', 'username', 'status', 'type', 'cookie']);
            if (isset($guestInformation['cookie'])) {
                $cookie = $guestInformation['cookie'];
                unset($guestInformation['cookie']);
                return response()->json([
                    'status' => 'success',
                    'self' => $guestInformation,
                ])->cookie($cookie);
            }
            return response()->json([
                'status' => 'success',
                'self' => $guestInformation,
            ]);
        }

        $userService->bootUser(Auth::guard('api')->user()->id);

        return response()->json([ 
            'status' => 'success',
            'self' => $userService->getUserInformation(Auth::guard('api')->user()->id, ['uid', 'username', 'color', 'status', 'type']), 
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
    public function sendMessage(MessageRequest $request, CommandService $commandService, GuestService $guestService): JsonResponse
    {

        // Send Message
        $fields = $request->validated();

        // For guest accounts
        if (!Auth::guard('api')->check()) {

            $guestInformation = $guestService->bootGuest();
            $response = $this->chatService->processMessage(null, $guestInformation['uid'], $fields['message'], $commandService);

            return response()->json($response);
        }

        // For logged in accounts
        $response = $this->chatService->processMessage(Auth::guard('api')->user(), 0, $fields['message'], $commandService);

        return response()->json($response);
    }
}
