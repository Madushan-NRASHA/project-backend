<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'user_type',
        'user_theme',
        'Profile_Pic'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // ------------------------
    // Relationships
    // ------------------------

    // User has many jobs they posted
    public function jobsPosted()
    {
        return $this->hasMany(Job::class, 'user_id');
    }

    // User has many messages sent
    public function messagesSent()
    {
        return $this->hasMany(Msg::class, 'sender_id');
    }

    // User has many messages received
    public function messagesReceived()
    {
        return $this->hasMany(Msg::class, 'receiver_id');
    }
}
