<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentProfile extends Model
{
    protected $fillable = ['user_id', 'full_name', 'student_no', 'contact_no', 'course_id', 'year_level', 'about'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function skills()
    {
        return $this->belongsToMany(Skill::class, 'student_skills');
    }

    public function experiences()
    {
        return $this->hasMany(StudentExperience::class);
    }

    public function availabilities()
    {
        return $this->hasMany(StudentAvailability::class);
    }

    public function dayAvailabilities()
    {
        return $this->hasMany(StudentDayAvailability::class);
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    // Load everything the matching algorithm needs in one go (fewer database queries)
    public function loadForMatching()
    {
        return $this->load(['skills', 'availabilities', 'dayAvailabilities', 'experiences']);
    }

    // Profile completion in percent
    public function completion()
    {
        $score = 20;                                              // course and year level are filled at registration
        if ($this->contact_no) $score += 15;
        if ($this->about) $score += 15;
        if ($this->skills()->count() > 0) $score += 20;
        if ($this->availabilities()->count() > 0) $score += 20;
        if ($this->experiences()->count() > 0) $score += 10;
        return $score;
    }
}
