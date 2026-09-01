<?php

namespace App\Http\Controllers\Api;

use App\Filters\EventFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\ListEventsRequest;
use App\Http\Resources\EventDetailResource;
use App\Http\Resources\EventListItemResource;
use App\Models\Event;
use App\Traits\ApiResponse;

class EventController extends Controller
{
    use ApiResponse;

    /**
     * Get list of Events
     *
     * Retrieve a paginated list of published events with dynamic filtering and sorting.
     *
     * @apiResourceCollection App\Http\Resources\EventListItemResource
     * @apiResourceModel App\Models\Event
     */
    public function index(ListEventsRequest $request)
    {
        $filters = $request->validated();
        $perPage = $request->input('perPage', 12);

        // Gunakan EventFilter (Query Object) dan Eager Load organization untuk mencegah N+1
        $query = EventFilter::apply(Event::query(), $filters)->with('organization');

        $events = $query->paginate($perPage);

        // Sesuai edge case MOK-25: 
        // Jika kosong, tetap return 200 OK dengan data []
        if ($events->isEmpty()) {
            return response()->json([
                'data' => [],
                'meta' => [
                    'current_page' => $events->currentPage(),
                    'last_page'    => $events->lastPage(),
                    'total'        => $events->total(),
                    'per_page'     => $events->perPage(),
                ]
            ], 200);
        }

        // Return pagination resource dengan EventListItemResource (tanpa description)
        return EventListItemResource::collection($events);
    }

    /**
     * Get Event Detail
     *
     * Retrieve the full details of a published event by its slug.
     *
     * @urlParam slug string required The slug of the event. Example: konser-musik-kemerdekaan
     * @apiResource App\Http\Resources\EventDetailResource
     * @apiResourceModel App\Models\Event
     */
    public function show($slug)
    {
        // Hanya ambil event jika status = published. 
        // Jika belum published atau tidak ada, return 404 (firstOrFail).
        $event = Event::with('organization')
            ->where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        return new EventDetailResource($event);
    }
}
