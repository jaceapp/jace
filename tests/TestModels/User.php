<?php

namespace JaceApp\Jace\Tests\TestModels;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable {
    use HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];
}
