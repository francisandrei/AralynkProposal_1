<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    public $timestamps = false;

    protected $fillable = ['name', 'is_approved'];

    public function students()
    {
        return $this->belongsToMany(StudentProfile::class, 'student_skills');
    }

    public function jobPosts()
    {
        return $this->belongsToMany(JobPost::class, 'job_post_skill');
    }

    // "Other skills" typed as text, for example "Welding, Sewing".
    // New skills are added to the skills table. Returns the ids of all the skills (maximum 5).
    public static function addFromText($text)
    {
        $ids = [];
        $count = 0;
        foreach (explode(',', (string) $text) as $name) {
            $name = trim($name);
            if (strlen($name) < 2 || strlen($name) > 60 || $count >= 5) continue;
            $skill = self::where('name', $name)->first();
            if (!$skill) {
                $skill = self::create(['name' => $name, 'is_approved' => true]);
            }
            $ids[] = $skill->id;
            $count++;
        }
        return $ids;
    }
}
