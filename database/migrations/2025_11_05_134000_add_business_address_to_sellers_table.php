<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('sellers') && !Schema::hasColumn('sellers', 'business_address')) {
            Schema::table('sellers', function (Blueprint $table) {
                $table->text('business_address')->nullable()->after('business_name');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('sellers') && Schema::hasColumn('sellers', 'business_address')) {
            Schema::table('sellers', function (Blueprint $table) {
                $table->dropColumn('business_address');
            });
        }
    }
};
