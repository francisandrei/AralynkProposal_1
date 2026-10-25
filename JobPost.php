<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class JobPost extends Model
{
    protected $fillable = [
        'business_profile_id', 'category_id', 'title', 'description', 'job_type', 'work_location', 'work_date', 'end_date',
        'start_time', 'end_time', 'slots', 'required_experience', 'additional_requirements', 'compensation', 'deadline',
        'status', 'is_hidden',
    ];

    public function businessProfile()
    {
        return $this->belongsTo(BusinessProfile::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function skills()
    {
        return $this->belongsToMany(Skill::class, 'job_post_skill');
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'job_post_course');
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    // Jobs students may see: open, not hidden, before the deadline, and the business account is active.
    // Use it like:  JobPost::open()->get()
    public function scopeOpen($query)
    {
        return $query->where('status', 'open')
            ->where('is_hidden', false)
            ->where('deadline', '>=', now()->toDateString())
            ->whereHas('businessProfile.user', function ($q) {
                $q->where('status', 'active');
            });
    }

    // Weekday numbers (1 = Monday ... 7 = Sunday) of a Recurring or Part-Time job
    public function dayNumbers()
    {
        return DB::table('job_post_days')->where('job_post_id', $this->id)->pluck('day_of_week')->toArray();
    }
}
