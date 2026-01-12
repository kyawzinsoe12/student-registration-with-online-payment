<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = [
        'major_id',
        'name',
        'description',
        'price',
        'slug',
        'image',
        'is_active',
        'created_by',
        'updated_by',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class,'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class,'updated_by');
    }

    public function major()
    {
        return $this->belongsTo(Major::class);
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class);
    }
}
