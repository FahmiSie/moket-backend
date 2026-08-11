<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_types', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            
            // Relasi ke event (karena tiket ini milik sebuah event)
            $table->foreignUuid('event_id')->constrained('events')->cascadeOnDelete();
            
            // Nama jenis tiket (misal: "Presale 1", "VIP")
            $table->string('name', 100);
            
            // Deskripsi benefit tiket (misal: "Dapat makan siang dan akses backstage")
            $table->text('description')->nullable();
            
            // Harga tiket. Menggunakan decimal agar presisi keuangan terjaga (maks 12 digit, 2 desimal)
            $table->decimal('price', 12, 2)->default(0);
            
            // Total kuota tiket yang disediakan panitia
            $table->integer('quota');
            
            // Jumlah tiket yang sudah laku dibayar. (Ini denormalisasi untuk meringankan query)
            $table->integer('quota_sold')->default(0);
            
            // Batasan maksimal pembelian per 1 user (default 5 tiket)
            $table->integer('max_per_user')->default(5);
            
            // Batas waktu tiket ini bisa dibeli (setelah ini lewat, statusnya Expired)
            $table->timestamp('expired_time');
            
            $table->timestamps();
            
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_types');
    }
};
