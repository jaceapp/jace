<?php

namespace JaceApp\Jace\Repositories;

use Carbon\Carbon;
use Illuminate\Support\Facades\Redis;
use JaceApp\Jace\Models\JaceGuest;
use Illuminate\Database\Eloquent\Collection;
use JaceApp\Jace\Enums\UserStatusEnum;

class JaceGuestRepository
{
    /**
     * Check if guest has a name
     *
     * @param string|null $token
     * @return boolean
     */
    public function doesGuestHaveAName(string|null $token): bool
    {
        if (is_null($token)) {
            return false;
        }

        return JaceGuest::where('uid', $token)
            ->exists();
    }

    /**
     * Find a guest by uid
     *
     * @param string $uid
     * @return array
     */
    public function findByToken(string $uid): array
    {
        $cacheKey = 'guest:' . $uid . ':profile';
        $profile = json_decode(Redis::get($cacheKey), true);
        if (!empty($profile)) {
            return $profile;
        }
        $guest = JaceGuest::with(['banned' => function($query) {
            $currentDate = Carbon::now();

            $query->where(function($query) use ($currentDate) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', $currentDate);
            });
        }])->where('uid', $uid)->first();

        if (empty($guest)) {
            return [];
        }

        $profile = [
            'id' => $guest->id,
            'uid' => $guest->uid,
            'username' => $guest->username,
            'status' => $this->accountStatus($guest->banned),
            'type' => 'guest',
        ];
        Redis::set($cacheKey, json_encode($profile));
        Redis::expire($cacheKey, config('jace.cache.users_profiles'));

        return $profile;
    }

    /**
     * Force cache refresh by deleting the key for findByToken
     * 
     * @param string $uid
     * @return array
     */
    public function forceFindByTokenRefresh(string $uid): array 
    {
        $cacheKey = 'guest:' . $uid . ':profile';
        Redis::del($cacheKey);

        return $this->findByToken($uid);
    }

    /**
     * Find a guest by username
     *
     * @param string $username
     * @return ?JaceGuest
     */
    public function findByName(string $username): ?JaceGuest
    {
        return JaceGuest::where('username', $username)->first();
    }

    /**
     * Check if the account is in good good-standing
     *
     * @param Collection @jaceBannedUser
     * @return string
     **/
    private function accountStatus(Collection $jaceBannedUser): string
    {
        if ($jaceBannedUser->isEmpty()) {
            return UserStatusEnum::GOOD_STANDING;
        }

        return $jaceBannedUser->pluck('type')->last();
    }
}
