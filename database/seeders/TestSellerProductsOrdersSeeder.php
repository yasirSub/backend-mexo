<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Carbon;

class TestSellerProductsOrdersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $email = 'seller1@test.com';

        // Ensure seller exists
        $seller = DB::table('sellers')->where('email', $email)->first();
        if (!$seller) {
            $sellerId = DB::table('sellers')->insertGetId([
                'name' => 'Seller One',
                'email' => $email,
                'password' => Hash::make('password'),
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $sellerId = $seller->id;
        }

        // Create sample products for this seller
        $products = [
            ['name' => 'Sample Product A', 'price' => 199.99, 'sku' => 'SPA-001'],
            ['name' => 'Sample Product B', 'price' => 349.50, 'sku' => 'SPB-002'],
            ['name' => 'Sample Product C', 'price' => 79.00, 'sku' => 'SPC-003'],
        ];

        $productIds = [];
        foreach ($products as $p) {
            // avoid duplicate SKU inserts
            $existing = DB::table('products')->where('sku', $p['sku'])->first();
            if ($existing) {
                $productIds[] = $existing->id;
                continue;
            }

            // include title for compatibility with schemas that expect it
            $productIds[] = DB::table('products')->insertGetId([
                'name' => $p['name'],
                'title' => $p['name'],
                'seller_id' => $sellerId,
                'price' => $p['price'],
                'sku' => $p['sku'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Insert orders across this month with different statuses
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $middleOfMonth = $now->copy()->startOfMonth()->addDays(10);
        $endOfMonth = $now->copy()->endOfMonth()->subDays(2);

        $orders = [
            // Pending (early month)
            ['product_id' => $productIds[0], 'amount' => 199.99, 'buyer_email' => 'buyer1@example.com', 'status' => 'Pending', 'created_at' => $startOfMonth->toDateTimeString()],
            ['product_id' => $productIds[1], 'amount' => 349.50, 'buyer_email' => 'buyer2@example.com', 'status' => 'Pending', 'created_at' => $startOfMonth->copy()->addDays(2)->toDateTimeString()],

            // Shipped (middle)
            ['product_id' => $productIds[0], 'amount' => 199.99, 'buyer_email' => 'buyer3@example.com', 'status' => 'Shipped', 'created_at' => $middleOfMonth->toDateTimeString()],
            ['product_id' => $productIds[2], 'amount' => 79.00, 'buyer_email' => 'buyer4@example.com', 'status' => 'Shipped', 'created_at' => $middleOfMonth->copy()->addDays(3)->toDateTimeString()],

            // Delivered (recent)
            ['product_id' => $productIds[1], 'amount' => 349.50, 'buyer_email' => 'buyer5@example.com', 'status' => 'Delivered', 'created_at' => $endOfMonth->toDateTimeString()],
            ['product_id' => $productIds[2], 'amount' => 79.00, 'buyer_email' => 'buyer6@example.com', 'status' => 'Delivered', 'created_at' => $now->toDateTimeString()],
        ];

        foreach ($orders as $o) {
            // Some schemas may not have product_id; insert only available fields
            $insert = ['seller_id' => $sellerId, 'created_at' => $o['created_at'], 'updated_at' => now()];

            if (Schema::hasColumn('orders', 'amount')) {
                $insert['amount'] = $o['amount'];
            }
            if (Schema::hasColumn('orders', 'buyer_email')) {
                $insert['buyer_email'] = $o['buyer_email'];
            }
            if (Schema::hasColumn('orders', 'status')) {
                $insert['status'] = $o['status'];
            }
            if (Schema::hasColumn('orders', 'product_id')) {
                $insert['product_id'] = $o['product_id'];
            }

            if (Schema::hasColumn('orders', 'order_number')) {
                // generate a unique order number
                do {
                    $candidate = 'ORD-' . strtoupper(substr(uniqid(), 0, 8));
                } while (DB::table('orders')->where('order_number', $candidate)->exists());
                $insert['order_number'] = $candidate;
            }

            DB::table('orders')->insert($insert);
        }
    }
}
