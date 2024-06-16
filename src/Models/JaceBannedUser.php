<?php

namespace JaceApp\Jace\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JaceBannedUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'guest_id',
        'type',
        'start_date',
        'end_date',
        'reason',
    ];
}
