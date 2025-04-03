<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
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
