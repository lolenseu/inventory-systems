<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_name',
        'type',
        'quantity',
        'status',
        'in_game_id',
        'request_date',
    ];

    protected $casts = [
        'request_date' => 'datetime',
    ];

    // Scope for user-specific equipment
    public function scopeForUser($query, $in_game_id)
    {
        return $query->where('in_game_id', $in_game_id);
    }
}
