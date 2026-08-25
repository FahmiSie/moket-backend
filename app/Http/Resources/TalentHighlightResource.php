<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TalentHighlightResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * Mapping: user.name → name, portfolio_url → portfolioUrl
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'name'         => $this->user->name,
            'category'     => $this->category,
            'bio'          => $this->bio,
            'portfolioUrl' => $this->portfolio_url,
        ];
    }
}
