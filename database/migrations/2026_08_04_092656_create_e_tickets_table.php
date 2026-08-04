<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('e_tickets', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            
            // Tiket ini dicetak dari rincian transaksi yang mana?
            $table->foreignUuid('transaction_item_id')->constrained('transaction_items')->cascadeOnDelete();
            
            // Tiket ini jenisnya apa? (Misal: VIP)
            $table->foreignUuid('ticket_type_id')->constrained('ticket_types')->restrictOnDelete();
            
            // Tiket ini untuk event apa? (Denormalisasi agar QR Scanner bisa mencari event dengan sangat cepat)
            $table->foreignUuid('event_id')->constrained('events')->cascadeOnDelete();
            
            // Siapa pemilik tiket ini?
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            
            // Nama orang yang akan hadir (Bisa jadi Budi membelikan tiket untuk temannya, si Andi)
            $table->string('attendee_name', 150);
            $table->string('attendee_email', 150)->nullable();
            
            // Kode unik (string acak panjang) yang akan disembunyikan di dalam gambar QR Code
            $table->string('qr_code', 255)->unique();
            
            // Status Check-in (false = belum hadir, true = sudah masuk)
            $table->boolean('is_used')->default(false);
            
            // Kapan tiket ini di-scan di gate?
            $table->timestamp('used_at')->nullable();
            
            // Siapa panitia yang melakukan scan tiket ini? 
            // (Jika panitia dihapus akunnya, set null saja agar tiket peserta tidak ikut terhapus)
            $table->foreignUuid('checked_in_by')->nullable()->constrained('users')->nullOnDelete();
            
            $table->timestamps();
            
            // Membuat Index (opsional tapi disarankan)
            // Karena QR Scanner akan mem-filter event_id dan is_used jutaan kali secara terus menerus,
            // menambahkan index akan mempercepat proses scan (menghindari antrean panjang di gerbang).
            $table->index(['event_id', 'is_used']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('e_tickets');
    }
};
