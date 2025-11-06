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
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('seller_id')->nullable()->after('order_id')->constrained('sellers')->onDelete('cascade');
            $table->boolean('seller_paid')->default(false)->after('status');
            $table->timestamp('seller_paid_at')->nullable()->after('seller_paid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['seller_id']);
            $table->dropColumn(['seller_id', 'seller_paid', 'seller_paid_at']);
        });
    }
};

