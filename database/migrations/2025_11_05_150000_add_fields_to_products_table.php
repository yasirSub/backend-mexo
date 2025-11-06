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
        if (!Schema::hasColumn('products', 'seller_id') || !Schema::hasColumn('products', 'price')) {
            Schema::table('products', function (Blueprint $table) {
                if (!Schema::hasColumn('products', 'seller_id')) {
                    $table->unsignedBigInteger('seller_id')->nullable()->after('id');
                }
                if (!Schema::hasColumn('products', 'price')) {
                    $table->decimal('price', 10, 2)->default(0)->after('seller_id');
                }
                if (!Schema::hasColumn('products', 'sku')) {
                    $table->string('sku')->nullable()->after('price');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'sku')) {
                $table->dropColumn('sku');
            }
            if (Schema::hasColumn('products', 'price')) {
                $table->dropColumn('price');
            }
            if (Schema::hasColumn('products', 'seller_id')) {
                $table->dropColumn('seller_id');
            }
        });
    }
};
