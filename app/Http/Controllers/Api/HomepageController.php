<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FeaturedEventResource;
use App\Http\Resources\SubOrganizationResource;
use App\Http\Resources\TalentHighlightResource;
use App\Services\HomepageService;
use App\Traits\ApiResponse;

class HomepageController extends Controller
{
    use ApiResponse;

    protected $homepageService;

    public function __construct(HomepageService $homepageService)
    {
        $this->homepageService = $homepageService;
    }

    /**
     * GET /api/homepage/featured-events
     * Maks 3 event published terdekat.
     */
    public function featuredEvents()
    {
        $events = $this->homepageService->featuredEvents();

        return $this->successResponse(
            'Featured events retrieved successfully.',
            FeaturedEventResource::collection($events)
        );
    }

    /**
     * GET /api/homepage/sub-organizations
     * Semua sub-organisasi aktif.
     */
    public function subOrganizations()
    {
        $organizations = $this->homepageService->subOrganizations();

        return $this->successResponse(
            'Sub-organizations retrieved successfully.',
            SubOrganizationResource::collection($organizations)
        );
    }

    /**
     * GET /api/homepage/talent-highlights
     * Talent yang punya bio, max 6.
     */
    public function talentHighlights()
    {
        $talents = $this->homepageService->talentHighlights();

        return $this->successResponse(
            'Talent highlights retrieved successfully.',
            TalentHighlightResource::collection($talents)
        );
    }
}
