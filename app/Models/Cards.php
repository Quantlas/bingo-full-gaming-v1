<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cards extends Model
{
    protected $table = 'cards';
    protected $primaryKey = 'id';

    protected $fillable = [
        'game_id',
        'user_id',
        'serial_number',
        'card_path',
        'status',
        'created_at',
        'updated_at',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
