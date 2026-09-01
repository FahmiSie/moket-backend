<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Transaction;
use App\Models\TicketType;
use Illuminate\Support\Facades\DB;

class ExpirePendingTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:expire-pending-transactions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Membatalkan transaksi pending yang melewati batas waktu 15 menit dan mengembalikan kuota tiket.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $expiredTime = now()->subMinutes(15);
        
        $pendingTransactions = Transaction::with('transactionItems.ticketType')
            ->where('status', 'pending')
            ->where('created_at', '<', $expiredTime)
            ->get();

        $count = 0;

        foreach ($pendingTransactions as $transaction) {
            DB::transaction(function () use ($transaction, &$count) {
                // Atomic Update untuk mencegah race condition dengan Webhook (MOK-33)
                $affected = Transaction::where('id', $transaction->id)
                    ->where('status', 'pending')
                    ->update(['status' => 'expired']);

                if ($affected > 0) {
                    $count++;
                    
                    // Kembalikan kuota untuk setiap jenis tiket di struk ini
                    foreach ($transaction->transactionItems as $item) {
                        if ($item->ticketType) {
                            // Karena ini mungkin bisa race condition juga, pakai atomik decrement atau query ulang
                            TicketType::where('id', $item->ticket_type_id)
                                ->decrement('quota_sold', $item->quantity);
                        }
                    }
                }
            });
        }

        $this->info("Berhasil meng-expire {$count} transaksi yang melewati batas waktu.");
    }
}
