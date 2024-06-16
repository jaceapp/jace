<?php

namespace JaceApp\Jace\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JaceOauthProvider extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'provider_name',
        'provider_user_id',
        'email',
        'username',
    ];

    public function user()
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'user_id', 'id');
    }
}
