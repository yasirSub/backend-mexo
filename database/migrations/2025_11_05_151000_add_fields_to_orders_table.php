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
        if (!Schema::hasColumn('orders', 'seller_id') || !Schema::hasColumn('orders', 'status')) {
            Schema::table('orders', function (Blueprint $table) {
                if (!Schema::hasColumn('orders', 'seller_id')) {
                    $table->unsignedBigInteger('seller_id')->nullable()->after('id');
                }
                if (!Schema::hasColumn('orders', 'product_id')) {
                    $table->unsignedBigInteger('product_id')->nullable()->after('seller_id');
                }
                if (!Schema::hasColumn('orders', 'amount')) {
                    $table->decimal('amount', 10, 2)->default(0)->after('product_id');
                }
                if (!Schema::hasColumn('orders', 'buyer_email')) {
                    $table->string('buyer_email')->nullable()->after('amount');
                }
                if (!Schema::hasColumn('orders', 'status')) {
                    $table->string('status')->default('Pending')->after('buyer_email');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('orders', 'buyer_email')) {
                $table->dropColumn('buyer_email');
            }
            if (Schema::hasColumn('orders', 'amount')) {
                $table->dropColumn('amount');
            }
            if (Schema::hasColumn('orders', 'product_id')) {
                $table->dropColumn('product_id');
            }
            if (Schema::hasColumn('orders', 'seller_id')) {
                $table->dropColumn('seller_id');
            }
        });
    }
};
