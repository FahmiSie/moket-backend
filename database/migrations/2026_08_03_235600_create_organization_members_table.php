<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organization_members', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            
            $table->foreignUuid('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            
            // Role Panitia HANYA berlaku di organisasi ini (Contextual Role)
            $table->enum('role', ['admin', 'committee', 'ticketing', 'scanner', 'finance'])->default('committee');
            
            // Alur Invite: invited -> (klik email) -> active
            $table->enum('status', ['invited', 'active', 'inactive'])->default('invited');
            $table->string('invitation_token', 100)->nullable()->unique();
            
            // Audit Trails: Melacak siapa mengundang siapa
            $table->foreignUuid('invited_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('invited_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('joined_at')->nullable();
            
            $table->timestamps();
            
            // Mencegah user yang sama ditambahkan 2 kali ke org yang sama
            $table->unique(['organization_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organization_members');
    }
};
