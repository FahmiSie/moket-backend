<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeaturedEventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * Mapping sesuai kontrak FE (MOK-16):
     *   title → name, banner_url → posterUrl, organization → organizer
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            'slug'      => $this->slug,
            'name'      => $this->title,
            'posterUrl'  => $this->banner_url,
            'startDate' => $this->start_date->toIso8601String(),
            'location'  => $this->location,
            'organizer' => [
                'name'    => $this->organization->name,
                'logoUrl' => $this->organization->logo_url,
            ],
        ];
    }
}
