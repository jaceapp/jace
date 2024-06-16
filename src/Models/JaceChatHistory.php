<?php

namespace JaceApp\Jace\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JaceChatHistory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'guest_id',
        'type',
        'message',
    ];
}
