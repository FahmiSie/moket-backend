<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            
            $table->string('name', 150);
            $table->string('slug', 170)->unique(); // Untuk URL: moket.id/org/osis-smk-telkom
            $table->string('logo_url', 255)->nullable();
            $table->text('description')->nullable();
            $table->string('contact_email', 150)->nullable();
            $table->string('contact_phone', 20)->nullable();
            
            $table->enum('status', ['active', 'inactive'])->default('active');
            
            // Siapa user yang pertama kali mendaftarkan org ini? (Untuk audit)
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            
            $table->timestamps();
            
            // Soft Deletes: Jika org dihapus, transaksi historis tiket tidak boleh error/hilang
            $table->softDeletes(); 
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};
