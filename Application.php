<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    protected $fillable = ['student_profile_id', 'job_post_id', 'message', 'status'];

    public function student()
    {
        return $this->belongsTo(StudentProfile::class, 'student_profile_id');
    }

    public function jobPost()
    {
        return $this->belongsTo(JobPost::class);
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    // The statuses a business may choose next. (The admin never decides, only the business.)
    public function nextStatuses()
    {
        if ($this->status == 'Pending') return ['Reviewed', 'Accepted', 'Rejected'];
        if ($this->status == 'Reviewed') return ['Accepted', 'Rejected'];
        if ($this->status == 'Accepted') return ['Completed', 'Rejected'];
        if ($this->status == 'Rejected') return ['Reviewed'];
        return [];
    }
}
