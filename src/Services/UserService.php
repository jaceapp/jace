<?php

namespace JaceApp\Jace\Services;

use Exception;
use Illuminate\Support\Arr;
use JaceApp\Jace\Repositories\JaceUserProfileRepository;
use JaceApp\Jace\Repositories\JaceGuestRepository;
use Illuminate\Support\Str;
use JaceApp\Jace\Enums\ColorEnums;
use JaceApp\Jace\Enums\ErrorEnums;
use JaceApp\Jace\Models\JaceUserProfile;

class UserService
{
    private $userProfileRepository;
    protected $yaceGuestRepository;

    public function __construct(JaceUserProfileRepository $userProfileRepository, JaceGuestRepository $yaceGuestRepository)
    {
        $this->userProfileRepository = $userProfileRepository;
        $this->yaceGuestRepository = $yaceGuestRepository;
    }

    /**
     * Looks for the username in members and guests
     *
     * @param string $username
     * @return array
     */
    public function findByName(string $username): array
    {
        $username = $this->removeAtSymbol($username);
        $userProfile = $this->userProfileRepository->findByName($username);
        if (empty($userProfile)) {
            return [
                'status' => 'error',
                'code' => ErrorEnums::USER_NOT_FOUND,
                'message' => 'User not found',
            ];
        }
        $userProfile['type'] = 'user';

        return $userProfile;
    }

    /**
     * Boot user, create the profile if it doesn't exist already, and return the profile, update chat list
     * 
     * @param int $userId
     * @return array
     */
    public function bootUser(int $userId): array
    {
        $userProfile = $this->userProfileRepository->find($userId);
        if (empty($userProfile)) {
            JaceUserProfile::create([
                'user_id' => $userId,
                'username' => $this->generateUniqueUsername('user'),
                'uid' => $this->generateUserToken(),
                'color' => $this->getRandomColor(),
            ]);


            return $this->userProfileRepository->forceFindRefresh($userId);
        }

        return $this->userProfileRepository->find($userId);
    }
    
    /**
     * Get user information
     * 
     * @param int $userId
     * @param array $select
     * @return array
     */
    public function getUserInformation(int $userId, array $select = [])
    {
        $userProfile = $this->userProfileRepository->find($userId);

        if (empty($select)) {
            return $userProfile;
        }

        return Arr::only($userProfile, $select);
    }

    /** 
     * Generate a unique username
     *
     * @param string $username
     * @param string $count
     * @return string
     **/
    public function generateUniqueUsername(string $username, $count = 0): string
    {
        $exists = JaceUserProfile::where('username', $username)->exists(); 
        if (!$exists) {
            return $username;
        }

        $newUsername = $username . $count;

        return $this->generateUniqueUsername($newUsername, $count+1);
    }

    /**
     * Generate user token
     *
     * @return string
     */
    public function generateUserToken(): string
    {
        return Str::uuid()->toString();
    }

    /**
     * Get random color
     *
     * @return string
     **/
    public function getRandomColor(): string
    {
        $keys = array_keys(ColorEnums::COLORS);

        $randomKey = array_rand($keys);

        $randomColour = ColorEnums::COLORS[$keys[$randomKey]];

        return $randomColour;
    }

    /**
     * Removes @ symbol from username
     *
     * @param string $username
     * @return string
     */
    private function removeAtSymbol(string $username): string
    {
        return str_replace('@', '', $username);
    }
}
