<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'slug' => $this->slug,
            'image' => $this->image ? asset('storage/' . $this->image) : 'No Image',
            'is_active' => $this->is_active ? 'Active' : 'Inactive',

            'major' => [
                'id' => $this->major->id ?? null,
                'name' => $this->major->name ?? null,
            ]
        ];
    }
}