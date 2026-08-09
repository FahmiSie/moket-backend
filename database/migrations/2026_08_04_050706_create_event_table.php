<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            
            // UBAH: Sekarang mengarah ke organizations, bukan users
            $table->foreignUuid('organization_id')->constrained('organizations')->cascadeOnDelete();
            
            // TAMBAH: Audit trails untuk melacak panitia mana yang membuat/mengedit
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('updated_by')->nullable()->constrained('users')->nullOnDelete();
            
            $table->string('title', 200);
            $table->string('slug', 220)->unique();
            $table->text('description')->nullable();
            $table->string('category', 100)->nullable();
            $table->string('location', 255)->nullable();
            
            $table->enum('scope', ['internal', 'external']);
            $table->timestamp('start_date');
            $table->timestamp('end_date');
            $table->string('banner_url', 255)->nullable();
            
            $table->enum('status', ['draft', 'published', 'completed', 'cancelled'])->default('draft');
            
            $table->timestamps();
            
            // TAMBAH: Soft Deletes
            $table->softDeletes();
            
            // Indexing untuk mempercepat pencarian event di Frontend
            $table->index(['status', 'start_date']);
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('events'); // Jangan lupa di sini juga diubah jadi 'events'
    }
};
