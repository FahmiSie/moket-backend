<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubOrganizationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * Mapping: logo_url → logoUrl (camelCase untuk FE)
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'slug'        => $this->slug,
            'logoUrl'     => $this->logo_url,
            'description' => $this->description,
        ];
    }
}
