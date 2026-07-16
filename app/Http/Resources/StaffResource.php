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

            'email' => $this->email,

            'role' => $this->role->value,

            'created_at' => $this->created_at?->toDateTimeString(),

            'profile' => [
                'phone' => $this->staffProfile?->phone,

                'bio' => $this->staffProfile?->bio,

                'experience_years' => $this->staffProfile?->experience_years,

                'employment_status' => $this->staffProfile?->employment_status instanceof \BackedEnum
                    ? $this->staffProfile->employment_status->value
                    : $this->staffProfile?->employment_status,

                'designation' => $this->staffProfile?->designation
                    ? new DesignationResource($this->staffProfile->designation)
                    : null,

                'designation_id' => $this->staffProfile?->designation_id,

                'profile_photo' => $this->staffProfile?->profile_photo
                    ? asset($this->staffProfile->profile_photo)
                    : null,
            ],

            'services' => $this->whenLoaded('services', function () {
                return $this->services->map(fn($srv) => [
                    'id'       => $srv->id,
                    'name'     => $srv->name,
                    'duration' => $srv->duration ?? null,
                ]);
            }),
        ];
    }
}
