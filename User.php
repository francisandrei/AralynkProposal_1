<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// A login account. role = student, business or admin.
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role', 'status'];

    protected $hidden = ['password', 'remember_token'];

    public function studentProfile()
    {
        return $this->hasOne(StudentProfile::class);
    }

    public function businessProfile()
    {
        return $this->hasOne(BusinessProfile::class);
    }

    public function appNotifications()
    {
        return $this->hasMany(Notification::class);
    }

    // Ratings other people gave to this user
    public function ratingsReceived()
    {
        return $this->hasMany(Rating::class, 'ratee_id');
    }

    // Average stars (1 to 5), or null if nobody rated yet
    public function averageRating()
    {
        return $this->ratingsReceived()->avg('stars');
    }
}
