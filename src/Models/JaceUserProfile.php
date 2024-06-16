<?php

namespace JaceApp\Jace\Models;

use JaceApp\Jace\Models\JaceBannedUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasRoles;

class JaceUserProfile extends Model
{
    use HasFactory, HasRoles;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'username',
        'uid',
        'color',
        'avatar',
    ];

    public function user()
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'user_id', 'id');
    }

    public function banned()
    {
      return $this->hasMany(JaceBannedUser::class, 'user_id', 'user_id');
    }
}
