<?php

namespace JaceApp\Jace\Services;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use JaceApp\Jace\Enums\ErrorEnums;
use JaceApp\Jace\Models\JaceGuest;
use JaceApp\Jace\Repositories\JaceGuestRepository;

class GuestService
{

    protected $guestRepository;

    public function __construct(JaceGuestRepository $guestRepository)
    {
        $this->guestRepository = $guestRepository;
    }

    /**
     * Process the guest message request.
     *
     * @return array
     */
    public function bootGuest(): array
    {
        // If there's a guest name set in the DB, and there's no cookie set, then generate both
        $doesGuestHaveAName = $this->guestRepository->doesGuestHaveAName(Cookie::get('guest_user_id'));
        if (!$doesGuestHaveAName) {
            $token = $this->generateGuestToken();
            $username = $this->generateGuestName();
            $ipAddress = request()->ip();
            $this->createGuestInformation($token, $username, $ipAddress);
            $guestInformation = $this->getGuestInformation($token);
            $guestInformation['cookie'] = cookie('guest_user_id', $guestInformation['uid'], 60 * 24, null, null, false, true);

            return $guestInformation;
        }

        // If there's a guest name set in the DB, and there's a cookie set, then grab the name from the DB
        $token = Cookie::get('guest_user_id');
        $guestInformation = $this->getGuestInformation($token);

        return $guestInformation;
    }

    /**
     * Get Guest Name, if it doesn't exist then create it.
     *
     * @param string $token
     * @param array $select
     * @return array|null
     */
    public function getGuestInformation(string $token, array $select = []): ?array
    {
        $userProfile = $this->guestRepository->findByToken($token);

        if (empty($select)) {
            return $userProfile;
        }

        return Arr::only($userProfile, $select);
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
        $guest = $this->guestRepository->findByName($username);

        if ($guest === null) {
            return [
                'status' => 'error',
                'code' => ErrorEnums::USER_NOT_FOUND,
                'message' => 'User not found',
            ];
        }

        $guest = $guest->toArray();
        $guest['type'] = 'guest';

        return $guest;
    }

    /**
     * Generate Guest Token
     *
     * @return string
     */
    private function generateGuestToken(): string
    {
        return Str::uuid()->toString();
    }

    /**
     * Generate Guest Names
     *
     * @return string
     */
    private function generateGuestName(): string
    {
        $usernames = [
            "PixelMaster",
            "ShadowHunter",
            "IronMerc",
            "NovaStar",
            "CyberWolf",
            "PhantomThief",
            "DragonRider",
            "SilverSaber",
            "NinjaValkyrie",
            "MysticWarrior",
            "ElectricOracle",
            "QuantumSlayer",
            "GalacticGuardian",
            "RuneSeeker",
            "SpaceVoyager",
            "TimelessWanderer",
            "EclipseKnight",
            "CosmicRaven",
            "FirePhoenix",
            "AstralWizard",
            "NeonSamurai",
            "FrostArcher",
            "StormChaser",
            "SkyPirate",
            "StealthBlade",
            "TerraTamer",
            "LunarDruid",
            "SolarSorcerer",
            "AetherAssassin",
            "VortexVanquisher",
            "ZenithZealot",
            "RebelRogue",
            "ChaosCatalyst",
            "DuskDuelist",
            "RadiantReaper",
            "BlazeBattler",
            "VoidVanguard",
            "OblivionOutlaw",
            "LegendSeeker",
            "MysticMarauder",
            "InfernoInvader",
            "PrismProtector",
            "GlitchGuru",
            "ByteBrawler",
            "DigitalDemon",
            "SpectralStriker",
            "ZephyrZapper",
            "WarpWarrior",
            "FlameFury",
            "IcyIllusionist",
            "ThunderTemplar",
            "CrimsonCrafter",
            "BlinkBlaster",
            "AquaAvenger",
            "RockRavager",
            "EchoEnchanter",
            "NebulaNinja",
            "MeteorMystic",
        ];

        return $usernames[array_rand($usernames)] . rand(1, 999);
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

    /**
     * Create Guest Information
     *
     * @param string $token
     * @param string $username
     * @param string $ipAddress
     * @return void
     */
    private function createGuestInformation(string $token, string $username, string $ipAddress): void
    {
        JaceGuest::create([
            'uid' => $token,
            'username' => $username,
            'ip_address' => $ipAddress,
        ]);
    }
}
