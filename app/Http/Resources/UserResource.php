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
            'email_verified_at' => $this->email_verified_at ? (is_string($this->email_verified_at) ? $this->email_verified_at : $this->email_verified_at->toDateTimeString()) : null,
            'created_at' => $this->created_at ? (is_string($this->created_at) ? $this->created_at : $this->created_at->toDateTimeString()) : null,
        ];
    }

}
