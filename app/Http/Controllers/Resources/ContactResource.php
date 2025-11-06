<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'company' => $this->company,
            'position' => $this->position,
            'status' => $this->status,
            'source' => $this->source,
            'assigned_user' => new UserResource($this->whenLoaded('assignedUser')),
            'notes' => $this->notes,
            'last_contacted' => $this->last_contacted,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}