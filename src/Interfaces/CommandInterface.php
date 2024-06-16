<?php

namespace JaceApp\Jace\Interfaces;

use Illuminate\Contracts\Auth\Authenticatable;

interface CommandInterface
{
    public function handle(array $args, Authenticatable $user): array;
}
