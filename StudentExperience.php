<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentExperience extends Model
{
    protected $fillable = ['student_profile_id', 'title', 'category_id', 'description'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
