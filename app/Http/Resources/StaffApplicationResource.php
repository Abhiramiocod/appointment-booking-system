<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StaffApplicationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user?->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'status' => $this->status->value,
            'employment_status' => $this->user?->staffProfile?->employment_status,
            'admin_notes' => $this->admin_notes,
            'approved_by' => $this->approver?->only([
                'id',
                'name',
            ]),
            'designation' => [
                'id' => $this->designation?->id,
                'name' => $this->designation?->name,
            ],
            'cover_letter' => $this->cover_letter,
            'experience_years' => $this->experience_years,
            'approved_at' => $this->approved_at?->toDateTimeString(),
            'rejected_by' => $this->rejector?->only([
                'id',
                'name',
            ]),
            'rejected_at' => $this->rejected_at?->toDateTimeString(),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
