<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'appointment_date' => $this->appointment_date,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,

            'status' => $this->status->value,

            'notes' => $this->notes,

            'customer' => $this->whenLoaded(
                'customer',
                fn () => [
                    'id' => $this->customer->id,
                    'name' => $this->customer->name,
                ]
            ),

            'staff' => $this->whenLoaded(
                'staff',
                fn () => [
                    'id' => $this->staff->id,
                    'name' => $this->staff->name,
                ]
            ),

            'service' => $this->whenLoaded(
                'service',
                fn () => [
                    'id' => $this->service->id,
                    'name' => $this->service->name,
                    'duration' => $this->service->duration,
                    'price' => $this->service->price,
                ]
            ),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
