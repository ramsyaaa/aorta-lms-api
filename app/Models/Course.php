<?php

namespace App\Models;

use Ramsey\Uuid\Uuid;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
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
        'title',
        'description',
        'image',
        'video',
        'number_of_meeting',
        'is_have_pretest_posttest',
        'instructor_uuid',
        'status',
    ];

    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_uuid', 'uuid');
    }

    public function lessons()
    {
        return $this->hasMany(CourseLesson::class);
    }

    public function pretestPosttests()
    {
        return $this->hasMany(PretestPosttest::class, 'course_uuid', 'uuid');
    }

    public function tags()
    {
        return $this->hasMany(CourseTag::class, 'course_uuid', 'uuid');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = Uuid::uuid4()->toString();
        });
    }
}
