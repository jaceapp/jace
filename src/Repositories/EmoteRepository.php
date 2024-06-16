<?php

namespace JaceApp\Jace\Repositories;

use Illuminate\Support\Collection;
use JaceApp\Jace\Models\JaceEmojiCatalog;
use Illuminate\Support\Facades\Redis;

class EmoteRepository
{
    /**
     * Grab all, and cache in redis
     *
     * @param array $select
     * @return array
     **/
    public function all(array $select = []): array
    {
        $cacheKey = $select ? 'emoji:list:'.implode(':', $select) : 'emoji:list:all';
        $emote = json_decode(Redis::get($cacheKey), true);
        if (!empty($emote)) {
            return $emote;
        }

        $emote = JaceEmojiCatalog::select($select)->get();

        Redis::set($cacheKey, json_encode($emote->toArray()));
        Redis::expire($cacheKey, config('jace.cache.emoji_all'));

        return $emote->toArray();
    }

    public function keyMap()
    {
        $cacheKey = 'emoji:keyMap';
        $keyMap = json_decode(Redis::get($cacheKey), true);
        if (!empty($keyMap)) {
            return $keyMap;
        }

        $emotes = new Collection($this->all());
        $transformed = $emotes->mapWithKeys(function($item) {
            return [$item['shortcode'] => $item['image_url']];
        })->toArray();

        Redis::set($cacheKey, json_encode($transformed));
        Redis::expire($cacheKey, config('jace.cache.users_profiles'));

        return $transformed;
    }
}
