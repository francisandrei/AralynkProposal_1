<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Weekly availability (one row per weekday)
class StudentAvailability extends Model
{
    public $timestamps = false;

    protected $fillable = ['student_profile_id', 'day_of_week', 'start_time', 'end_time'];
}
