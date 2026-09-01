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
     * Get Featured Events
     * 
     * Retrieve a maximum of 3 upcoming published events for the homepage.
     * Response is cached for 5 minutes.
     *
     * @group Homepage
     * @apiResourceCollection App\Http\Resources\FeaturedEventResource
     * @apiResourceModel App\Models\Event
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
     * Get Sub Organizations
     * 
     * Retrieve all active sub-organizations (Mitra).
     * Response is cached for 5 minutes.
     *
     * @group Homepage
     * @apiResourceCollection App\Http\Resources\SubOrganizationResource
     * @apiResourceModel App\Models\Organization
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
     * Get Talent Highlights
     * 
     * Retrieve up to 6 talents that have a bio filled out.
     * Response is cached for 5 minutes.
     *
     * @group Homepage
     * @apiResourceCollection App\Http\Resources\TalentHighlightResource
     * @apiResourceModel App\Models\TalentProfile
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
