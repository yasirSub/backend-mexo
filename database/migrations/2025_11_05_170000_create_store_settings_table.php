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
        if (!Schema::hasTable('store_settings')) {
            Schema::create('store_settings', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('seller_id')->unique();
                $table->boolean('pickup_enabled')->default(false);
                $table->decimal('min_order_amount', 10, 2)->default(0);
                $table->text('shipping_policy')->nullable();
                $table->string('support_email')->nullable();
                $table->string('contact_phone')->nullable();
                $table->json('opening_hours')->nullable();
                $table->boolean('auto_accept_orders')->default(false);
                $table->decimal('delivery_radius_km', 6, 2)->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_settings');
    }
};
