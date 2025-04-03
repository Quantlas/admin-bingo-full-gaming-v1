<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payments extends Model
{

    protected $connection = 'bingo-game';
    protected $table = 'payments';
    protected $fillable = [
        'user_id',
        'amount',
        'payable_type',
        'payable_id',
        'amount',
        'payment_method',
        'reference',
        'status'
    ];
}
