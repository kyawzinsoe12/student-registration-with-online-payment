<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EnrollmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => [
                'id' => $this->user->id ?? null,
                'name' => $this->user->name ?? null,
                'email' => $this->user->email ?? null,
            ],
            'course' => [
                'id' => $this->course->id ?? null,
                'name' => $this->course->name ?? null,
                'description' => $this->course->description ?? null,
            ],
            'status' => $this->status,
            'enrolled_at' => $this->enrolled_at ? $this->enrolled_at->toDateTimeString() : null,
            'completed_at' => $this->completed_at ? $this->completed_at->toDateTimeString() : null,
        ];
    }
}