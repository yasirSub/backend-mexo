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
        if (Schema::hasTable('sellers') && !Schema::hasColumn('sellers', 'profile_picture')) {
            Schema::table('sellers', function (Blueprint $table) {
                $table->string('profile_picture')->nullable()->after('business_address');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('sellers') && Schema::hasColumn('sellers', 'profile_picture')) {
            Schema::table('sellers', function (Blueprint $table) {
                $table->dropColumn('profile_picture');
            });
        }
    }
};
