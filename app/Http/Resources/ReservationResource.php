<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReservationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *  Recurso para transformar la respuesta de la API en Json y controlar cuales son los datos que se van a enviar 
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'meeting_room_id' => $this->meeting_room_id,
            'user_id' => $this->user_id,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'version' => $this->version,
            'created_at' => $this->created_at
        ];
    }
}
