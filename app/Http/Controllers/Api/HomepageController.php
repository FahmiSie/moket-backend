<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FeaturedEventResource;
use App\Models\Event;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Cache;

class HomepageController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/homepage/featured-events
     *
     * Mengembalikan maks 3 event unggulan yang published dan belum lewat,
     * diurutkan berdasarkan start_date terdekat.
     * Response di-cache 60 detik untuk mengurangi beban database.
     */
    public function featuredEvents()
    {
        $events = Cache::remember('homepage:featured-events', 60, function () {
            return Event::with('organization')
                ->where('status', 'published')
                ->where('start_date', '>=', now())
                ->orderBy('start_date', 'asc')
                ->limit(3)
                ->get();
        });

        return $this->successResponse(
            'Featured events retrieved successfully.',
            FeaturedEventResource::collection($events)
        );
    }
}
