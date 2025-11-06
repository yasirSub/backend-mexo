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
        if (Schema::hasTable('sellers')) {
            Schema::table('sellers', function (Blueprint $table) {
                if (!Schema::hasColumn('sellers', 'business_name')) {
                    $table->string('business_name')->nullable()->after('name');
                }
                if (!Schema::hasColumn('sellers', 'phone')) {
                    $table->string('phone')->nullable()->after('business_name');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('sellers')) {
            Schema::table('sellers', function (Blueprint $table) {
                if (Schema::hasColumn('sellers', 'phone')) {
                    $table->dropColumn('phone');
                }
                if (Schema::hasColumn('sellers', 'business_name')) {
                    $table->dropColumn('business_name');
                }
            });
        }
    }
};
