<?php

namespace JaceApp\Jace\Repositories;

use App\Models\User;
use Illuminate\Support\Facades\Redis;
use JaceApp\Jace\Models\JaceUserProfile;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use JaceApp\Jace\Enums\UserStatusEnum;

class JaceUserProfileRepository
{

    /**
     * Find profile, if not found then create it
     *
     * @param integer $userId
     * @return array
     */
    public function find(int $userId): array
    {
        $cacheKey = 'user:' . $userId . ':profile';
        $profile = json_decode(Redis::get($cacheKey), true);
        // Get user profile
        if (!empty($profile)) {
           return $profile;
        }

        // Get user profile
        $userProfile = JaceUserProfile::with(['user', 'banned' => function($query) {
            $currentDate = Carbon::now();
            
            $query->where(function($query) use ($currentDate) {
                $query->whereNull('end_date')
                      ->orWhere('end_date', '>=', $currentDate);
            });

        }])->where('user_id', $userId)->first();

        if (empty($userProfile)) {
            return [];
        }

        $profile = [
            'id' => $userProfile->user_id,
            'uid' => $userProfile->uid,
            'username' => $userProfile->username ?? $userProfile->user->name,
            'color' => $userProfile->color,
            'avatar' => $userProfile->user->avatar,
            'status' => $this->accountStatus($userProfile->banned),
            'type' => 'user',
        ];

        // Cache results
        Redis::set($cacheKey, json_encode($profile));
        Redis::expire($cacheKey, config('jace.cache.users_profiles'));

        return $profile;
    }

    /**
     * Force refresh find. Always call this method when making updates to users_profiles
     *
     * @param integer $userId
     * @return array
     */
    public function forceFindRefresh(int $userId): array
    {
        $cacheKey = 'user:' . $userId . ':profile';
        Redis::del($cacheKey);

        return $this->find($userId);
    }

    /**
     * Find a user by their username
     *
     * @param string $username
     * @return array
     */
    public function findByName(string $username): array
    {
        $user = JaceUserProfile::where('username', $username)->first();

        if (empty($user)) {
            return [];
        }

        return $this->find($user->id);
    }

    /**
     * Does name exist?
     *
     * @param string $username
     * @return bool
     **/
    public function doesNameExist(string $username): bool
    {
        return JaceUserProfile::where('username', $username)->exists();
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
