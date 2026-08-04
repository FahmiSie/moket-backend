<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaction_items', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            
            // Relasi ke struk utama (jika struk dihapus, rincian ini ikut terhapus)
            $table->foreignUuid('transaction_id')->constrained('transactions')->cascadeOnDelete();
            
            // Relasi ke jenis tiket yang dibeli
            // Pakai restrictOnDelete() agar panitia tidak bisa menghapus jenis tiket yang sudah ada pembelinya
            $table->foreignUuid('ticket_type_id')->constrained('ticket_types')->restrictOnDelete();
            
            // Jumlah tiket yang dibeli untuk jenis ini
            $table->integer('quantity');
            
            // Snapshot harga tiket SAAT dibeli (agar jika panitia mengubah harga tiket besoknya, harga di struk lama tidak berubah)
            $table->decimal('price', 12, 2);
            
            // Subtotal = quantity * price
            $table->decimal('subtotal', 12, 2);
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_items');
    }
};
