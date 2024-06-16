<?php

namespace JaceApp\Jace\Repositories;

use JaceApp\Jace\Models\JaceOauthProvider;

class JaceOauthProviderRepository
{
    /**
     * Find if a record with the provider and id exists
     *
     * @param string $providerName
     * @param string $id
     * @return bool
     **/
    public function exists(string $providerName, string $id): bool
    {
        return JaceOauthProvider::where('provider_name', $providerName)
            ->where('provider_user_id', $id)
            ->exists();
    }

    /**
     * Find a record by provider name and id
     *
     * @param string $providerName
     * @param string $id
     * @param array $select
     * @return ?JaceOauthProvider
     **/
    public function findById(string $providerName, string $id, array $select = ['user_id']): ?JaceOauthProvider
    {
        return JaceOauthProvider::select($select)->where('provider_name', $providerName)
            ->where('provider_user_id', $id)
            ->first();
    }
}
