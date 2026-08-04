<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));

            $table->foreignUuid('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('full_name', 150);
            $table->string('phone', 20)->nullable();
            $table->string('school_origin', 150)->nullable();
            $table->string('class_batch', 100)->nullable();

            $table->string('avatar_url', 255)->nullable;

            $table->enum('category', ['Internal','External'])->default('Internal');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};
