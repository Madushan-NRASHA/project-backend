<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Get_job extends Model
{
    use HasFactory;

    protected $table = 'get_job';

    protected $fillable = ['job_name', 'user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
