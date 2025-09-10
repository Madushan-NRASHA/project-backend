<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Msg extends Model
{
    use HasFactory;

    protected $table = 'msgs';

    protected $fillable = [
        'job_id',
        'sender_id',
        'receiver_id',
        'message',
        'extra_data',
    ];

    protected $casts = [
        'extra_data' => 'array',
    ];

    // ------------------------
    // Relationships
    // ------------------------

    // Message belongs to a Job
    public function job()
    {
        return $this->belongsTo(Job::class, 'job_id');
    }

    // Message sender (User)
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    // Message receiver (User)
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}
