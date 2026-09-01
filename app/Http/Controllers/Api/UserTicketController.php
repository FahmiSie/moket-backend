<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use App\Services\TicketService;

class UserTicketController extends Controller
{
    /**
     * Get all active tickets for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $tickets = Ticket::whereHas('transactionItem.transaction', function ($query) {
            $query->where('user_id', auth()->id())
                  ->where('status', 'success');
        })
        ->with(['event', 'ticketType'])
        ->orderBy('created_at', 'desc')
        ->get();

        return response()->json([
            'success' => true,
            'data' => $tickets
        ]);
    }

    /**
     * Get details of a specific ticket including its QR code.
     */
    public function show(Ticket $ticket, TicketService $ticketService): JsonResponse
    {
        // Prevent IDOR: Abort with 404 if the user doesn't own this ticket
        abort_unless($ticket->transactionItem->transaction->user_id === auth()->id(), 404, 'Ticket not found.');

        $ticket->load(['event', 'ticketType']);

        return response()->json([
            'success' => true,
            'data' => [
                'ticket' => $ticket,
                'qr_code_base64' => $ticketService->generateQrCodeBase64($ticket)
            ]
        ]);
    }
}
