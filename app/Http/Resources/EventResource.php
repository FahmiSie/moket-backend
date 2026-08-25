<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * Konversi field snake_case DB ke camelCase sesuai kontrak FE MOK-22
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'slug'        => $this->slug,
            'name'        => $this->title,
            'posterUrl'   => $this->banner_url,
            'description' => $this->description,
            'category'    => $this->category,
            'location'    => $this->location,
            'scope'       => $this->scope,
            'startDate'   => $this->start_date ? $this->start_date->toIso8601String() : null,
            'endDate'     => $this->end_date ? $this->end_date->toIso8601String() : null,
            'organizer'   => [
                'id'      => $this->organization->id,
                'name'    => $this->organization->name,
                'logoUrl' => $this->organization->logo_url,
            ],
        ];
    }
}
