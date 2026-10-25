<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessProfile extends Model
{
    protected $fillable = ['user_id', 'business_name', 'contact_person', 'contact_no', 'address', 'category_id', 'description'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function jobPosts()
    {
        return $this->hasMany(JobPost::class);
    }
}
