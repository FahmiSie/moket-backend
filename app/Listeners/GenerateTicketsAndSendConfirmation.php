<?php

namespace App\Listeners;

use App\Events\TransactionPaid;
use App\Models\Ticket;
use App\Mail\TicketConfirmationMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class GenerateTicketsAndSendConfirmation implements ShouldQueue
{
    use InteractsWithQueue;

    public $tries = 3;

    public function backoff(): array
    {
        return [30, 60, 120];
    }

    /**
     * Handle the event.
     */
    public function handle(TransactionPaid $event): void
    {
        $transaction = $event->transaction->load('transactionItems.ticketType', 'user', 'event');

        // 1. Idempotency Guard: Cek apakah tiket sudah dibuat untuk transaksi ini
        $alreadyIssued = Ticket::whereHas('transactionItem', 
            fn ($q) => $q->where('transaction_id', $transaction->id)
        )->exists();

        if ($alreadyIssued) {
            return;
        }

        // 2. Atomic Ticket Generation
        DB::transaction(function () use ($transaction) {
            foreach ($transaction->transactionItems as $item) {
                for ($i = 0; $i < $item->quantity; $i++) {
                    Ticket::create([
                        'transaction_item_id' => $item->id,
                        'event_id' => $transaction->event_id,
                        'ticket_type_id' => $item->ticket_type_id,
                        'code' => Str::random(32),
                        'status' => 'valid',
                    ]);
                }
            }
        });

        // 3. Ambil ulang semua tiket yang baru dibuat
        $tickets = Ticket::whereHas('transactionItem', 
            fn ($q) => $q->where('transaction_id', $transaction->id)
        )->with('ticketType')->get();

        // 4. Kirim Email (di luar DB transaction agar jika gagal, tiket tetap sah)
        Mail::to($transaction->user->email)->send(new TicketConfirmationMail($transaction, $tickets));
    }
}
