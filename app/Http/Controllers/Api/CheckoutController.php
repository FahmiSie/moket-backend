<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CheckoutRequest;
use App\Models\Event;
use App\Services\CheckoutService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class CheckoutController extends Controller
{
    use ApiResponse;

    protected $checkoutService;

    public function __construct(CheckoutService $checkoutService)
    {
        $this->checkoutService = $checkoutService;
    }

    /**
     * Checkout Tiket
     *
     * Membuat pesanan tiket (transaction) baru untuk event tertentu.
     * Mengamankan kuota dengan database locking dan validasi batas maksimal per user.
     * 
     * @group Transactions
     * @authenticated
     * 
     * @response 201 {
     *   "success": true,
     *   "message": "Pesanan berhasil dibuat.",
     *   "data": {
     *     "id": "uuid",
     *     "invoice_number": "INV-MOKET-20260901-XXXXXX",
     *     "total_amount": "150000.00",
     *     "status": "pending",
     *     "snap_token": null,
     *     "transaction_items": [
     *       {
     *         "ticket_type_id": "uuid",
     *         "quantity": 2,
     *         "price": "75000.00",
     *         "subtotal": "150000.00"
     *       }
     *     ]
     *   }
     * }
     * @response 400 {
     *   "success": false,
     *   "message": "Kuota tiket tidak mencukupi.",
     *   "data": null
     * }
     */
    public function store(CheckoutRequest $request, Event $event): JsonResponse
    {
        try {
            $transaction = $this->checkoutService->processCheckout(
                auth()->user(),
                $event,
                $request->validated('tickets'),
                $request->validated('payment_method')
            );

            return $this->successResponse("Pesanan berhasil dibuat.", $transaction, 201);
        } catch (\Exception $e) {
            $statusCode = $e->getCode();
            $statusCode = ($statusCode >= 400 && $statusCode < 600) ? $statusCode : 500;
            return $this->errorResponse($e->getMessage(), [], $statusCode);
        }
    }
}
