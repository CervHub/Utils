<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Thread extends Model
{

    protected $table = 'threads';

    protected $fillable = [
        'thread_id',
        'apikey',
        'user_id',
        'expires_at',
    ];
}
