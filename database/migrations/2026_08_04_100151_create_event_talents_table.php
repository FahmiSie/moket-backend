<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        Schema::create('event_talents', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignUuid('talent_id')->constrained('users')->cascadeOnDelete();
            $table->date('performance_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->enum('status', ['draft', 'scheduled', 'ready', 'performed'])->default('draft');
            $table->timestamps();
            $table->unique(['event_id', 'talent_id']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('event_talents');
    }
};
