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
        // Add columns to sellers table
        if (Schema::hasTable('sellers')) {
            Schema::table('sellers', function (Blueprint $table) {
                if (!Schema::hasColumn('sellers', 'user_id')) {
                    $table->foreignId('user_id')->nullable()->after('id')->constrained()->onDelete('cascade');
                }
                if (!Schema::hasColumn('sellers', 'business_name')) {
                    $table->string('business_name')->nullable()->after('name');
                }
                if (!Schema::hasColumn('sellers', 'contact_person')) {
                    $table->string('contact_person')->nullable()->after('business_name');
                }
                if (!Schema::hasColumn('sellers', 'phone')) {
                    $table->string('phone')->nullable()->after('email');
                }
                if (!Schema::hasColumn('sellers', 'address')) {
                    $table->text('address')->nullable();
                }
                if (!Schema::hasColumn('sellers', 'city')) {
                    $table->string('city')->nullable();
                }
                if (!Schema::hasColumn('sellers', 'state')) {
                    $table->string('state')->nullable();
                }
                if (!Schema::hasColumn('sellers', 'pincode')) {
                    $table->string('pincode')->nullable();
                }
                if (!Schema::hasColumn('sellers', 'gstin')) {
                    $table->string('gstin')->nullable();
                }
                if (!Schema::hasColumn('sellers', 'pan')) {
                    $table->string('pan')->nullable();
                }
                if (!Schema::hasColumn('sellers', 'status')) {
                    $table->enum('status', ['pending', 'active', 'inactive'])->default('pending');
                }
                if (!Schema::hasColumn('sellers', 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        }

        // Add columns to products table
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                if (!Schema::hasColumn('products', 'seller_id')) {
                    $table->foreignId('seller_id')->nullable()->after('id')->constrained()->onDelete('cascade');
                }
                if (!Schema::hasColumn('products', 'title')) {
                    $table->string('title');
                }
                if (!Schema::hasColumn('products', 'description')) {
                    $table->text('description')->nullable();
                }
                if (!Schema::hasColumn('products', 'price')) {
                    $table->decimal('price', 10, 2)->default(0);
                }
                if (!Schema::hasColumn('products', 'category')) {
                    $table->string('category')->nullable();
                }
                if (!Schema::hasColumn('products', 'stock_quantity')) {
                    $table->integer('stock_quantity')->default(0);
                }
                if (!Schema::hasColumn('products', 'sku')) {
                    $table->string('sku')->nullable()->unique();
                }
                if (!Schema::hasColumn('products', 'status')) {
                    $table->enum('status', ['pending', 'active', 'inactive'])->default('pending');
                }
                if (!Schema::hasColumn('products', 'images')) {
                    $table->json('images')->nullable();
                }
                if (!Schema::hasColumn('products', 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        }

        // Add columns to orders table
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (!Schema::hasColumn('orders', 'seller_id')) {
                    $table->foreignId('seller_id')->nullable()->after('id')->constrained()->onDelete('cascade');
                }
                if (!Schema::hasColumn('orders', 'user_id')) {
                    $table->foreignId('user_id')->nullable()->after('seller_id')->constrained()->onDelete('cascade');
                }
                if (!Schema::hasColumn('orders', 'order_number')) {
                    $table->string('order_number')->unique();
                }
                if (!Schema::hasColumn('orders', 'total_amount')) {
                    $table->decimal('total_amount', 10, 2)->default(0);
                }
                if (!Schema::hasColumn('orders', 'status')) {
                    $table->enum('status', ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'])->default('pending');
                }
                if (!Schema::hasColumn('orders', 'payment_status')) {
                    $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
                }
                if (!Schema::hasColumn('orders', 'payment_method')) {
                    $table->string('payment_method')->nullable();
                }
                if (!Schema::hasColumn('orders', 'payout_status')) {
                    $table->enum('payout_status', ['pending', 'processing', 'completed', 'not_applicable'])->default('not_applicable');
                }
                if (!Schema::hasColumn('orders', 'shipping_address')) {
                    $table->json('shipping_address')->nullable();
                }
                if (!Schema::hasColumn('orders', 'notes')) {
                    $table->text('notes')->nullable();
                }
                if (!Schema::hasColumn('orders', 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        }

        // Create order_items table if it doesn't exist
        if (!Schema::hasTable('order_items')) {
            Schema::create('order_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained()->onDelete('cascade');
                $table->foreignId('product_id')->constrained()->onDelete('cascade');
                $table->integer('quantity')->default(1);
                $table->decimal('price', 10, 2)->default(0);
                $table->decimal('total', 10, 2)->default(0);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop order_items table
        Schema::dropIfExists('order_items');

        // Remove columns from orders table
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                $columns = ['seller_id', 'user_id', 'order_number', 'total_amount', 'status', 
                           'payment_status', 'payment_method', 'payout_status', 'shipping_address', 'notes', 'deleted_at'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('orders', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        // Remove columns from products table
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                $columns = ['seller_id', 'title', 'description', 'price', 'category', 
                           'stock_quantity', 'sku', 'status', 'images', 'deleted_at'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('products', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        // Remove columns from sellers table
        if (Schema::hasTable('sellers')) {
            Schema::table('sellers', function (Blueprint $table) {
                $columns = ['user_id', 'business_name', 'contact_person', 'phone', 'address', 
                           'city', 'state', 'pincode', 'gstin', 'pan', 'status', 'deleted_at'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('sellers', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
