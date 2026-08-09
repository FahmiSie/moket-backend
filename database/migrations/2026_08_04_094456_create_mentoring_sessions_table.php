<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mentoring_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            
            // Sesi mentoring ini diperuntukkan untuk jadwal talent yang mana?
            $table->foreignUuid('event_talent_id')->constrained('event_talents')->cascadeOnDelete();
            
            // Siapa mentor yang mengajar di sesi ini? (Relasi ke tabel users dengan role 'mentor')
            $table->foreignUuid('mentor_id')->constrained('users')->cascadeOnDelete();
            
            // Jadwal dan Durasi
            $table->timestamp('scheduled_at');
            $table->integer('duration_minutes')->default(60); // Asumsi durasi standar 1 jam (60 menit)
            
            // Integrasi Zoom API
            $table->string('zoom_meeting_id', 100)->nullable();
            $table->string('zoom_join_url', 255)->nullable();
            
            // Status Sesi
            $table->enum('status', ['scheduled', 'completed', 'cancelled'])->default('scheduled');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mentoring_sessions');
    }
};
