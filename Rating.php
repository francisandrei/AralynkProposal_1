<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// A 1 to 5 star rating. A business rates a student, or a student rates a business, after a job is Completed.
class Rating extends Model
{
    protected $fillable = ['application_id', 'rater_id', 'ratee_id', 'rater_role', 'stars', 'comment'];

    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    public function rater()
    {
        return $this->belongsTo(User::class, 'rater_id');
    }

    public function ratee()
    {
        return $this->belongsTo(User::class, 'ratee_id');
    }
}
