<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Portfolio extends Model
{
    use HasFactory;

    protected $table = '_portfolio'; // This tells Laravel to use your real table name

    protected $fillable = [
        'name',
        'user_id',
        'type',
        'contact_details',
        'projects',
        'review',
    ];

    public function projects()
    {
        return $this->hasMany(Project::class, 'portfolio_id');
    }
}
