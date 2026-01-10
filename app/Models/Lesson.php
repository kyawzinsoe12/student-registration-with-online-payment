<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    protected $fillable = [
        'course_id',
        'title',
        'slug',
        'content',
        'order',
        'is_free',
        'created_by',
        'updated_by'
    ];

    public function course()
    {
        return $this->belongsTo(Course::class); 
    }
}
