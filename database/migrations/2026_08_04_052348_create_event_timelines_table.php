<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_timelines', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            
            // Relasi ke event (karena timeline ini milik sebuah event)
            $table->foreignUuid('event_id')->constrained('events')->cascadeOnDelete();
            
            // Nama milestone, contoh: "Open Gate Presale 1"
            $table->string('milestone_name', 150);
            
            // Tanggal milestone tersebut
            $table->timestamp('milestone_date');
            
            // Status pencapaian
            $table->enum('status', ['pending', 'in_progress', 'completed'])->default('pending');
            
            // Catatan tambahan (jika ada)
            $table->text('notes')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_timelines');
    }
};
