<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = ['sender_id', 'receiver_id', 'job_post_id', 'body', 'is_read'];

    public function jobPost()
    {
        return $this->belongsTo(JobPost::class);
    }
}
