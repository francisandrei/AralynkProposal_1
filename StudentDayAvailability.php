<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// "Available for this day" (one specific date)
class StudentDayAvailability extends Model
{
    public $timestamps = false;

    protected $fillable = ['student_profile_id', 'avail_date', 'start_time', 'end_time'];
}
