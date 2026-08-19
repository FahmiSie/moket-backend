<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Organization;
use Illuminate\Http\Request;

/**
 * TEMPORARY TESTING INFRASTRUCTURE — MOK-11
 * Controller ini dibuat khusus untuk menguji Policy authorization.
 * BUKAN production domain endpoint. Hapus setelah testing selesai
 * atau ganti dengan controller domain yang sesungguhnya.
 */
class TestAuthorizationController extends Controller
{
    // === Organization Endpoints ===

    public function viewOrganization(Request $request, Organization $organization)
    {
        $this->authorize('view', $organization);

        return response()->json([
            'success' => true,
            'message' => 'Authorized to view organization.',
            'data' => ['organization_id' => $organization->id],
        ]);
    }

    public function updateOrganization(Request $request, Organization $organization)
    {
        $this->authorize('update', $organization);

        return response()->json([
            'success' => true,
            'message' => 'Authorized to update organization.',
            'data' => ['organization_id' => $organization->id],
        ]);
    }

    public function manageMembers(Request $request, Organization $organization)
    {
        $this->authorize('manageMembers', $organization);

        return response()->json([
            'success' => true,
            'message' => 'Authorized to manage members.',
            'data' => ['organization_id' => $organization->id],
        ]);
    }

    // === Event Endpoints ===

    public function createEvent(Request $request, Organization $organization)
    {
        // EventPolicy::create menerima organizationId karena Event belum ada
        $this->authorize('create', [Event::class, $organization->id]);

        return response()->json([
            'success' => true,
            'message' => 'Authorized to create event.',
            'data' => ['organization_id' => $organization->id],
        ]);
    }

    public function viewEvent(Request $request, Event $event)
    {
        $this->authorize('view', $event);

        return response()->json([
            'success' => true,
            'message' => 'Authorized to view event.',
            'data' => ['event_id' => $event->id],
        ]);
    }

    public function updateEvent(Request $request, Event $event)
    {
        $this->authorize('update', $event);

        return response()->json([
            'success' => true,
            'message' => 'Authorized to update event.',
            'data' => ['event_id' => $event->id],
        ]);
    }

    public function deleteEvent(Request $request, Event $event)
    {
        $this->authorize('delete', $event);

        return response()->json([
            'success' => true,
            'message' => 'Authorized to delete event.',
            'data' => ['event_id' => $event->id],
        ]);
    }

    public function checkInEvent(Request $request, Event $event)
    {
        $this->authorize('checkIn', $event);

        return response()->json([
            'success' => true,
            'message' => 'Authorized to check-in.',
            'data' => ['event_id' => $event->id],
        ]);
    }

    public function manageTickets(Request $request, Event $event)
    {
        $this->authorize('manageTickets', $event);

        return response()->json([
            'success' => true,
            'message' => 'Authorized to manage tickets.',
            'data' => ['event_id' => $event->id],
        ]);
    }
}
