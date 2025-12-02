<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Primary key: internal auto-increment id.
     */
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    /**
     * Mass assignable attributes.
     */
    protected $fillable = [
        'user_id',
        'guild_id',
        'in_game_name',
        'player_id',
        'password',
        'role',
        'verification_status',
        'verified_at',
    ];

    /**
     * Hidden attributes for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Attribute casts.
     */
    protected $casts = [
        'password' => 'hashed',
        'verified_at' => 'datetime',
    ];

    /**
     * Booted method to set user_id = id automatically after creation.
     */
    protected static function booted()
    {
        static::created(function ($user) {
            if (!$user->user_id) {
                $user->user_id = $user->id;
                $user->save();
            }
        });
    }

    /**
     * Custom login username for Auth::attempt.
     */
    public function getAuthIdentifierName(): string
    {
        return 'in_game_name';
    }

    /**
     * Relationship: user belongs to a guild.
     */
    public function guild()
    {
        return $this->belongsTo(Guild::class, 'guild_id', 'guild_id');
    }

    /**
     * Relationship: user has many requests.
     */
    public function requests()
    {
        return $this->hasMany(RequestDump::class, 'user_id', 'user_id');
    }
}
