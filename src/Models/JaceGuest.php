<?php

namespace JaceApp\Jace\Models;

use JaceApp\Jace\Models\JaceBannedUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JaceGuest extends Model
{
    use HasFactory;

    protected $fillable = [
        'uid',
        'username',
        'ip_address',
    ];

    public function banned()
    {
        return $this->hasMany(JaceBannedUser::class, 'guest_id', 'id');
    }
}
