<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('talent_profiles', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            
            $table->string('category', 100)->nullable(); // e.g., Band, MC, Solo
            $table->text('bio')->nullable();
            $table->string('portfolio_url', 255)->nullable();
            $table->string('contact_info', 150)->nullable();
            
            $table->timestamps();
            
            // Relasi 1:1, pastikan user_id unik
            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('talent_profiles');
    }
};

//
