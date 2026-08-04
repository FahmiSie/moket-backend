<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            
            // Relasi ke user yang melakukan aktivitas.
            // PENTING: nullable() dan nullOnDelete(). Jika user dihapus dari sistem, log aktivitasnya TIDAK BOLEH ikut terhapus (untuk bukti audit).
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            
            // Jenis aktivitas (misal: 'login', 'ticket_purchase', 'check_in')
            $table->string('activity_type', 50);
            
            // Detail aktivitas (misal: "Budi berhasil check-in di konser XYZ")
            $table->text('description')->nullable();
            
            // Catat IP Address (45 karakter cukup untuk IPv6) dan Browser/Perangkat (User Agent)
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            
            // Log hanya bertambah (append-only), tidak ada data log yang di-update.
            // Jadi kita cukup pakai created_at, tidak perlu updated_at.
            $table->timestamp('created_at')->useCurrent();
            
            // Indexes: Mempercepat Admin saat mem-filter log (misal mencari semua aktivitas 'login' hari ini)
            $table->index(['activity_type', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
