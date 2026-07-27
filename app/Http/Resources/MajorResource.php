<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MajorResource extends JsonResource
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
            'slug' => $this->slug,
            'description' => $this->description ?? 'No Description',
            'image_url' => $this->image ? asset('storage/' . $this->image) : 'No Image',
            'status' => $this->is_active ? 'Active' : 'In Active',
        ];

    }
}
