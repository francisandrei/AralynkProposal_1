<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobReport extends Model
{
    protected $fillable = ['job_post_id', 'reporter_id', 'reason', 'status'];

    public function jobPost()
    {
        return $this->belongsTo(JobPost::class);
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }
}
