<?php

namespace App\Models;
use Ramsey\Uuid\Uuid;

use Illuminate\Database\Eloquent\Model;

class StudentProgress extends Model
{
    public $incrementing = false; // Non-incrementing primary key
    protected $keyType = 'string'; // Primary key type is string
    protected $primaryKey = 'uuid'; // Name of the UUID column

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_uuid',
        'course_uuid',
        'lesson_uuid',
        'lecture_uuid',
    ];

    public static function isLectureDone($lectureUuid, $userUuid)
    {
        return self::where([
            'lecture_uuid' => $lectureUuid,
            'user_uuid' => $userUuid,
        ])->exists();
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = Uuid::uuid4()->toString();
        });
    }
}
