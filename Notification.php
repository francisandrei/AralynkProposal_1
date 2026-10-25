<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = ['user_id', 'type', 'message', 'link', 'is_read'];

    // Quick way to notify a user:  Notification::send($userId, 'application', 'Text', 'student/applications');
    public static function send($userId, $type, $message, $link = null)
    {
        return self::create(['user_id' => $userId, 'type' => $type, 'message' => substr($message, 0, 255), 'link' => $link]);
    }
}
