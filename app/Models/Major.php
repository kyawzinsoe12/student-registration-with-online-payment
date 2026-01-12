<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Major extends Model
{
    protected $fillable = [
        'id',
        'name',
        'slug',
        'description',
        'image',
        'is_active',
        'created_by',
        'updated_by',
    ];

        public function courses()
    {
        return $this->hasmany(Course::class);
    }
}


