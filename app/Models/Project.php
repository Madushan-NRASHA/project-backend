<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
       
        'user_id',       // added for relation to User
        'name',
        'description',
        'photo',
        'link'
    ];

    /**
     * Each project belongs to a portfolio.
     */
    public function portfolio()
    {
        return $this->belongsTo(Portfolio::class);
    }

    /**
     * Each project belongs to a user.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
