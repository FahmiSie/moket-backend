<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventListItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'slug'        => $this->slug,
            'name'        => $this->title,
            'posterUrl'   => $this->banner_url,
            // Description tidak perlu dibawa ke list view untuk menghemat bandwidth
            'category'    => $this->category,
            'location'    => $this->location,
            'scope'       => $this->scope,
            'startDate'   => $this->start_date ? $this->start_date->toIso8601String() : null,
            'endDate'     => $this->end_date ? $this->end_date->toIso8601String() : null,
            'organizer'   => [
                'id'      => $this->organization->id ?? null,
                'name'    => $this->organization->name ?? null,
                'logoUrl' => $this->organization->logo_url ?? null,
            ],
        ];
    }
}
