<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThemythDump extends Model
{
    use HasFactory;

    protected $table = 'request_dump';

    // Primary key
    protected $primaryKey = 'id';

    // Allow mass assignment
    protected $fillable = [
        'user_id',
        'request_id',
        'status',
        'note',
        'request_date',
        'action',
        'created_at',
        'updated_at',
    ];

    // Cast timestamps and request_date to Carbon instances
    protected $casts = [
        'request_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationship: The request belongs to a user.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id'); 
    }
}
