<?php

namespace App\Models;
use Ramsey\Uuid\Uuid;

use Illuminate\Database\Eloquent\Model;

class Subcategory extends Model
{
    public $incrementing = false; // Non-incrementing primary key
    protected $keyType = 'string'; // Primary key type is string
    protected $primaryKey = 'uuid'; // Name of the UUID column

    protected $fillable = [
        'category_uuid',
        'name',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = Uuid::uuid4()->toString();
        });
    }
}
