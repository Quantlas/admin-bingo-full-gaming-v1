<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cards extends Model
{
    protected $connection = 'bingo-game';
    protected $table = 'cards';
    protected $fillable = [
        'game_id',
        'user_id',
        'serial_number',
        'numbers',
        'status',
        'created_at',
        'updated_at',
    ];
}
