<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $imageUrl = null;
        if ($this->image) {
            $imageUrl = str_starts_with($this->image, 'http')
                ? $this->image
                : asset('storage/'.$this->image);
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'role' => $this->role->value,
            'image' => $imageUrl,
            'provider' => $this->provider ? ($this->provider->value ?? $this->provider) : 'local',
            'provider_id' => $this->provider_id,
            'has_password' => ! empty($this->password),

            'phone' => $this->phone,
            'dob' => $this->dob ? (is_string($this->dob) ? $this->dob : $this->dob->format('Y-m-d')) : null,
            'gender' => $this->gender,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->country,
            'postal_code' => $this->postal_code,
            'bio' => $this->bio,
            'preferred_contact_method' => $this->preferred_contact_method ?? 'email',
            'emergency_contact' => $this->emergency_contact,
            'medical_notes' => $this->medical_notes,
            'preferred_language' => $this->preferred_language ?? 'English',
            'staff_profile' => $this->staffProfile ? [
                'id' => $this->staffProfile->id,
                'designation_id' => $this->staffProfile->designation_id,
                'designation_name' => $this->staffProfile->designation?->name,
                'experience_years' => $this->staffProfile->experience_years,
                'specialization' => $this->staffProfile->specialization,
                'license_number' => $this->staffProfile->license_number,
                'working_since' => $this->staffProfile->working_since ? (is_string($this->staffProfile->working_since) ? $this->staffProfile->working_since : $this->staffProfile->working_since->format('Y-m-d')) : null,
                'employment_status' => $this->staffProfile->employment_status ? ($this->staffProfile->employment_status->value ?? $this->staffProfile->employment_status) : null,
            ] : null,
            'email_verified_at' => $this->email_verified_at ? (is_string($this->email_verified_at) ? $this->email_verified_at : $this->email_verified_at->toDateTimeString()) : null,
            'created_at' => $this->created_at ? (is_string($this->created_at) ? $this->created_at : $this->created_at->toDateTimeString()) : null,
        ];
    }
}
