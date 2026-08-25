<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Services\EventService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class EventController extends Controller
{
    use ApiResponse;

    protected $eventService;

    public function __construct(EventService $eventService)
    {
        $this->eventService = $eventService;
    }

    /**
     * GET /api/events
     * Endpoint untuk Discovery Event (Search, Filter, Sort, Pagination)
     */
    public function index(Request $request)
    {
        // Ambil semua query parameters
        $filters = $request->only([
            'q', 'category', 'subOrg', 'scope', 
            'dateFrom', 'dateTo', 'sort'
        ]);

        // Lempar ke service layer
        $events = $this->eventService->searchAndFilter($filters);

        // Jika kosong (empty state)
        if ($events->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'No events found matching the criteria.',
                'data' => [],
                'meta' => [
                    'current_page' => $events->currentPage(),
                    'last_page' => $events->lastPage(),
                    'total' => $events->total(),
                ]
            ], 200);
        }

        // Return pagination resource yang dimapping ke EventResource
        return EventResource::collection($events)->additional([
            'success' => true,
            'message' => 'Events retrieved successfully.'
        ]);
    }
}
