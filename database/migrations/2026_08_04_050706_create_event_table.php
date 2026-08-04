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
            
            // 2. PRIMARY KEY HARUS ADA! Jangan dihapus ya.
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            
            $table->foreignUuid('organization_id')->constrained('users')->cascadeOnDelete();
            
            // 3. title (bukan tittle)
            $table->string('title', 200);
            
            // 4. slug, description, category, location
            $table->string('slug', 220)->unique();
            $table->text('description')->nullable();
            $table->string('category', 100)->nullable();
            $table->string('location', 255)->nullable();
            
            // 5. scope (internal/external)
            $table->enum('scope', ['internal', 'external']);
            
            // 6. Waktu event dan banner
            $table->timestamp('start_date');
            $table->timestamp('end_date');
            $table->string('banner_url', 255)->nullable();
            
            // 7. Status
            $table->enum('status', ['draft', 'published', 'completed', 'cancelled'])->default('draft');
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events'); // Jangan lupa di sini juga diubah jadi 'events'
    }
};
