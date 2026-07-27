<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LessonResource extends JsonResource
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
            'title' => $this->title,
            'slug' => $this->slug,
            'content' => $this->content,
            'order' => $this->order,
            'is_free' => $this->is_free ? 'That is free' : 'That not free',

            'course_id' => [
                'id' => $this->id,
                'name' => $this->name,
            ]
        ];
    }
}
