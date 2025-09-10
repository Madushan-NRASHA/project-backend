<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    protected $table = 'get_job';

    protected $fillable = [
        'job_name',
        'job_catogary',
        'description',
        'user_id',
        'location',
        'salary_range',
        'job_type',
    ];

    // ------------------------
    // Relationships
    // ------------------------

    // Job belongs to a user (poster)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Job has many messages
    public function messages()
    {
        return $this->hasMany(Msg::class, 'job_id');
    }

    // ------------------------
    // Scopes
    // ------------------------

    public static function getJobCategories(): array
    {
        return self::whereNotNull('job_catogary')
            ->distinct()
            ->orderBy('job_catogary')
            ->pluck('job_catogary')
            ->toArray();
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('job_catogary', $category);
    }

    public function scopeByCategories($query, array $categories)
    {
        return $query->whereIn('job_catogary', $categories);
    }
}
