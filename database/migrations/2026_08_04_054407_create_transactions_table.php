<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            
            // Siapa yang membeli tiket ini?
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            
            // Tiket ini untuk event apa? (Ini denormalisasi untuk pelaporan Admin yang lebih cepat)
            $table->foreignUuid('event_id')->constrained('events')->cascadeOnDelete();
            
            // Nomor struk unik, misal: "INV-MOKET-20260804-001"
            $table->string('invoice_number', 50)->unique();
            
            // Total harga yang harus dibayar
            $table->decimal('total_amount', 12, 2);
            
            // Status pembayaran Midtrans
            $table->enum('status', ['pending', 'success', 'failed', 'expired'])->default('pending');
            
            // Token unik dari Midtrans untuk menampilkan popup pembayaran (Snap UI)
            $table->string('snap_token', 255)->nullable();
            
            // Metode pembayaran yang dipilih user (misal: "gopay", "bca_va", "qris")
            $table->string('payment_method', 50)->nullable();
            
            // Kapan tiket ini lunas dibayar?
            $table->timestamp('paid_at')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
