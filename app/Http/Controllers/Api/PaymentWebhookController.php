<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\MidtransService;
use App\Models\Transaction;
use App\Models\TicketType;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PaymentWebhookController extends Controller
{
    protected $midtransService;

    public function __construct(MidtransService $midtransService)
    {
        $this->midtransService = $midtransService;
    }

    /**
     * Handle Midtrans Payment Notification (Webhook)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function handle(Request $request): JsonResponse
    {
        try {
            $payload = $request->all();

            // 1. Log setiap notifikasi masuk untuk audit trail
            Log::info('Midtrans Webhook Received', [
                'order_id' => $payload['order_id'] ?? null,
                'transaction_status' => $payload['transaction_status'] ?? null,
                'fraud_status' => $payload['fraud_status'] ?? null,
            ]);

            // 2. Verifikasi Signature
            if (!$this->midtransService->verifySignature($payload)) {
                Log::warning('Midtrans Webhook: Invalid Signature', ['order_id' => $payload['order_id'] ?? null]);
                return response()->json(['message' => 'Invalid signature'], 200); // Harus tetap 200
            }

            $orderId = $payload['order_id'];
            $transactionStatus = $payload['transaction_status'];
            $fraudStatus = $payload['fraud_status'] ?? null;

            // 3. Mapping Transaction Status
            $newStatus = null;

            if ($transactionStatus == 'capture') {
                if ($fraudStatus == 'accept') {
                    $newStatus = 'success';
                } else if ($fraudStatus == 'challenge') {
                    // TETAP pending, butuh review manual
                    $newStatus = 'pending';
                }
            } else if ($transactionStatus == 'settlement') {
                $newStatus = 'success';
            } else if (in_array($transactionStatus, ['deny', 'cancel', 'expire'])) {
                $newStatus = 'failed';
                if ($transactionStatus == 'expire') {
                    $newStatus = 'expired';
                }
            } else if ($transactionStatus == 'pending') {
                $newStatus = 'pending';
            }

            // Jika status tidak perlu diubah, balas 200
            if (!$newStatus || $newStatus === 'pending') {
                return response()->json(['message' => 'Status unchanged or requires manual review'], 200);
            }

            // 4. Atomic Update & Idempotency
            DB::transaction(function () use ($orderId, $newStatus) {
                // Siapkan data update
                $updateData = ['status' => $newStatus];
                
                // Jika sukses, catat waktu pembayaran
                if ($newStatus === 'success') {
                    $updateData['paid_at'] = now();
                }

                // Gunakan atomic update agar webhook duplikat tidak memproses dua kali
                $affected = Transaction::where('invoice_number', $orderId)
                    ->where('status', 'pending')
                    ->update($updateData);

                // Jika update berhasil (artinya ini notifikasi pertama) dan status gagal/expired
                if ($affected > 0 && in_array($newStatus, ['failed', 'expired'])) {
                    $transaction = Transaction::with('transactionItems')->where('invoice_number', $orderId)->first();
                    
                    if ($transaction) {
                        // Kembalikan kuota
                        foreach ($transaction->transactionItems as $item) {
                            TicketType::where('id', $item->ticket_type_id)
                                ->decrement('quota_sold', $item->quantity);
                        }
                    }
                }
            });

            return response()->json(['message' => 'ok'], 200);

        } catch (\Exception $e) {
            Log::error('Midtrans Webhook Error: ' . $e->getMessage());
            // SELALU balas 200 agar Midtrans tidak retry
            return response()->json(['message' => 'ok'], 200);
        }
    }
}
