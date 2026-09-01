<?php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Support\Facades\Log;

class MidtransService
{
    public function __construct()
    {
        // Konfigurasi Midtrans
        \Midtrans\Config::$serverKey = config('services.midtrans.server_key');
        \Midtrans\Config::$isProduction = config('services.midtrans.is_production', false);
        \Midtrans\Config::$isSanitized = true;
        \Midtrans\Config::$is3ds = true;
    }

    /**
     * Membuat Snap Transaction ke API Midtrans.
     * Mengembalikan array yang berisi token dan redirect_url.
     *
     * @param Transaction $transaction
     * @return array
     */
    public function createSnapTransaction(Transaction $transaction): array
    {
        // Menyiapkan payload item_details berdasarkan transaction_items
        $itemDetails = [];
        foreach ($transaction->transactionItems as $item) {
            $itemDetails[] = [
                'id'       => $item->ticketType->id,
                'price'    => (int) $item->price, // Harus integer di Midtrans
                'quantity' => $item->quantity,
                'name'     => substr($item->ticketType->name, 0, 50), // Maksimal 50 karakter
            ];
        }

        // Setup payload utama
        $params = [
            'transaction_details' => [
                'order_id'     => $transaction->invoice_number,
                'gross_amount' => (int) $transaction->total_amount, // Sesuai harga snapshot
            ],
            'item_details' => $itemDetails,
            'customer_details' => [
                'first_name' => $transaction->user->name,
                'email'      => $transaction->user->email,
            ],
        ];

        try {
            // Panggil API Midtrans untuk membuat Snap token
            $snapToken = \Midtrans\Snap::getSnapToken($params);
            
            return [
                'token' => $snapToken,
                // Kita juga bisa return redirect_url jika mau mengarahkan user tanpa popup
                'redirect_url' => "https://app.sandbox.midtrans.com/snap/v3/redirection/{$snapToken}"
            ];
        } catch (\Exception $e) {
            Log::error('Midtrans Create Transaction Error: ' . $e->getMessage());
            throw new \Exception('Gagal membuat transaksi pembayaran.');
        }
    }

    /**
     * Verifikasi Signature Key dari Webhook Midtrans
     *
     * @param array $payload
     * @return bool
     */
    public function verifySignature(array $payload): bool
    {
        $orderId = $payload['order_id'] ?? '';
        $statusCode = $payload['status_code'] ?? '';
        $grossAmount = $payload['gross_amount'] ?? '';
        $serverKey = config('services.midtrans.server_key');

        $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);
        $providedSignature = $payload['signature_key'] ?? '';

        // Gunakan hash_equals untuk mencegah timing attack
        return hash_equals($expectedSignature, $providedSignature);
    }
}
