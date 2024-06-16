<?php

namespace JaceApp\Jace\Services;

use Illuminate\Support\Facades\Redis;
use JaceApp\Jace\Repositories\EmoteRepository;

// TODO Change to Emoji
class EmoteService
{
    protected $emoteRepository;

    public function __construct(EmoteRepository $emoteRepository)
    {
        $this->emoteRepository = $emoteRepository;
    }

    /**
     * Grabs all emojis from the table
     *
     * @param array $select
     * @return array
     **/
    public function all(array $select = []): array
    {
        return $this->emoteRepository->all($select);
    }

    // @DEPRECATED no longer needed
    public function keyMap()
    {
        return $this->emoteRepository->keyMap();
    }


    // @DEPRECATED no longer needed
    public function generateEmojiRegex(): string
    {
        $cacheKey = 'emoji:emojiRegex';
        $keyMapProcessed = json_decode(Redis::get($cacheKey), true);
        if (!empty($keyMapProcessed)) {
            return $keyMapProcessed;
        }

        $keyMap = $this->keyMap();
        $shortcodes = array_map('preg_quote', array_keys($keyMap));
        $pattern = implode('|', $shortcodes);

        Redis::set($cacheKey, json_encode($pattern));
        Redis::expire($cacheKey, 0); // TODO needs config

        return $pattern;
    }
}
