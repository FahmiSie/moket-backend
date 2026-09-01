<?php

namespace App\Services;

use App\Models\Event;
use App\Models\TicketType;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Str;

class CheckoutService
{
    protected $midtransService;

    public function __construct(MidtransService $midtransService)
    {
        $this->midtransService = $midtransService;
    }

    /**
     * @param User $user
     * @param Event $event
     * @param array $tickets
     * @param string|null $paymentMethod
     * @return Transaction
     * @throws Exception
     */
    public function processCheckout(User $user, Event $event, array $tickets, ?string $paymentMethod = null): Transaction
    {
        // 1. Agregasi duplikat dalam payload request (contoh: [{id: 1, qty: 1}, {id: 1, qty: 1}] -> [1 => 2])
        $aggregatedTickets = [];
        foreach ($tickets as $ticket) {
            $ticketTypeId = $ticket['ticket_type_id'];
            $qty = (int) $ticket['quantity'];
            
            if (isset($aggregatedTickets[$ticketTypeId])) {
                $aggregatedTickets[$ticketTypeId] += $qty;
            } else {
                $aggregatedTickets[$ticketTypeId] = $qty;
            }
        }

        $ticketTypeIds = array_keys($aggregatedTickets);

        return DB::transaction(function () use ($user, $event, $aggregatedTickets, $ticketTypeIds, $paymentMethod) {
            // 2. Lock for update dengan orderBy('id') untuk mencegah Deadlock
            $ticketTypes = TicketType::whereIn('id', $ticketTypeIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            // Cek apakah ada ticket type yang tidak ditemukan (mungkin dihapus saat request berjalan)
            if ($ticketTypes->count() !== count($ticketTypeIds)) {
                throw new Exception("Beberapa tipe tiket tidak ditemukan atau tidak tersedia.", 400);
            }

            $totalAmount = 0;
            $transactionItemsData = [];

            foreach ($aggregatedTickets as $ticketTypeId => $qty) {
                $ticketType = $ticketTypes[$ticketTypeId];

                // 3. Validasi Ownership Event (Cegah eksploitasi URL event A beli tiket event B)
                if ($ticketType->event_id !== $event->id) {
                    throw new Exception("Tiket '{$ticketType->name}' tidak berlaku untuk event ini.", 400);
                }

                // 4. Validasi Waktu
                if (now()->greaterThanOrEqualTo($ticketType->expired_time)) {
                    throw new Exception("Waktu pembelian untuk tiket '{$ticketType->name}' telah berakhir.", 400);
                }

                // 5. Validasi Kuota
                $sisaKuota = $ticketType->quota - $ticketType->quota_sold;
                if ($qty > $sisaKuota) {
                    throw new Exception("Kuota tiket '{$ticketType->name}' tidak mencukupi. Sisa kuota: {$sisaKuota}.", 400);
                }

                // 6. Validasi Limit Per User
                $riwayatPembelian = TransactionItem::where('ticket_type_id', $ticketTypeId)
                    ->whereHas('transaction', function ($query) use ($user) {
                        $query->where('user_id', $user->id)
                            ->whereIn('status', ['pending', 'success']);
                    })
                    ->sum('quantity');

                if (($riwayatPembelian + $qty) > $ticketType->max_per_user) {
                    $sisaLimit = $ticketType->max_per_user - $riwayatPembelian;
                    throw new Exception("Anda melebihi batas pembelian untuk tiket '{$ticketType->name}'. Sisa jatah Anda: {$sisaLimit}.", 400);
                }

                // Hitung total dan subtotal berdasarkan harga snapshot
                $subtotal = $qty * $ticketType->price;
                $totalAmount += $subtotal;

                // Tambahkan ke array untuk insert nanti
                $transactionItemsData[] = [
                    'id' => Str::uuid(),
                    'ticket_type_id' => $ticketTypeId,
                    'quantity' => $qty,
                    'price' => $ticketType->price,
                    'subtotal' => $subtotal,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // Kurangi kuota (atau tepatnya tambah kuota terjual)
                $ticketType->quota_sold += $qty;
                $ticketType->save();
            }

            // 7. Generate Invoice Number unik
            $invoiceNumber = 'INV-MOKET-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));

            // 8. Buat Transaksi (Pending)
            $transaction = Transaction::create([
                'user_id' => $user->id,
                'event_id' => $event->id,
                'invoice_number' => $invoiceNumber,
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'payment_method' => $paymentMethod,
            ]);

            // Tambahkan transaction_id ke array transaction_items
            foreach ($transactionItemsData as &$itemData) {
                $itemData['transaction_id'] = $transaction->id;
            }

            // 9. Buat TransactionItems
            TransactionItem::insert($transactionItemsData);

            // 10. MOK-33: Request ke API Midtrans untuk mendapatkan snap_token
            $snapData = $this->midtransService->createSnapTransaction($transaction->load('transactionItems.ticketType', 'user'));
            
            // Simpan snap token ke database
            $transaction->snap_token = $snapData['token'];
            $transaction->save();

            return $transaction;
        });
    }
}
