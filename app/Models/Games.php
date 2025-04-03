<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Games extends Model
{
    protected $connection = 'bingo-game';
    protected $table = 'games';
    protected $fillable = [
        'name',
        'description',
        'start_time',
        'price_per_card',
        'total_cards',
        'cards_per_user',
        'status',
        'prizes'
    ];
}
