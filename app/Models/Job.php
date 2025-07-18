<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Job extends Model // ✅ Rename class to Job
{
    use HasFactory;

    protected $table = 'get_job'; // Keep table name if DB table is still named 'get_job'

    protected $fillable = ['job_name',
        'user_id',
        'Description',
        'job_catogary',];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
