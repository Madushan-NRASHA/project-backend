<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'portfolio_id',
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
}
