<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
        public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // Karena kita sering memfilter status = published
            $table->index('status');
            
            // Karena kita memfilter rentang tanggal dan mengurutkannya (nearest)
            $table->index('start_date');
            
            // Karena kita sering memfilter kategori dan organisasi
            $table->index('category');
            $table->index('organization_id');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['start_date']);
            $table->dropIndex(['category']);
            $table->dropIndex(['organization_id']);
        });
    }

};
