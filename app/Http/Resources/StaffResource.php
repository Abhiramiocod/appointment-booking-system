<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StaffResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,

            'profile' => [
                'phone' => $this->staffProfile?->phone,
                'bio' => $this->staffProfile?->bio,
                'experience_years' => $this->staffProfile?->experience_years,
                'profile_image' => $this->staffProfile?->profile_image,
            ],
        ];
    }
}
