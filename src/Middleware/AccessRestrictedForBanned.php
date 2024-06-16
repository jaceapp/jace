<?php

namespace JaceApp\Jace\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;
use JaceApp\Jace\Repositories\JaceGuestRepository;
use JaceApp\Jace\Repositories\JaceUserProfileRepository;

class AccessRestrictedForBanned
{
   const BAN_STATUSES = ['ban', 'timeout'];

    protected $guestRepository;
    protected $userRepository;

    public function __construct(JaceGuestRepository $guestRepository, JaceUserProfileRepository $userProfile)
    {
        $this->guestRepository = $guestRepository;
        $this->userRepository = $userProfile;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::user()) {
            if (!Cookie::has('guest_user_id')) {
                return $next($request);
            }

            $guestProfile = $this->guestRepository->findByToken(Cookie::get('guest_user_id'));
            if (in_array($guestProfile['status'], self::BAN_STATUSES)) {
                return response()->json(['message' => 'Your account is banned.'], 403);
            }

            return $next($request);
        }

        // Registered User
        $userProfile = $this->userRepository->find(Auth::user()->id);
        if (in_array($userProfile['status'], self::BAN_STATUSES)) {
            return response()->json(['message' => 'Your account is banned.'], 403);
        }

        return $next($request);
    }
}
