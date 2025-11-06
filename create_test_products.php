<?php

use Illuminate\Support\Facades\DB;
use App\Models\Seller;
use App\Models\Product;

// Create test products for seller1
$seller = Seller::where('email', 'seller1@test.com')->first();

if ($seller) {
    Product::create([
        'seller_id' => $seller->id,
        'title' => 'Samsung Galaxy S23',
        'description' => 'Latest Samsung flagship phone with amazing camera',
        'price' => 74999,
        'stock_quantity' => 25,
        'category' => 'electronics',
        'status' => 'active',
        'sku' => 'SAMS23',
    ]);

    Product::create([
        'seller_id' => $seller->id,
        'title' => 'Apple AirPods Pro',
        'description' => 'Wireless earbuds with noise cancellation',
        'price' => 24900,
        'stock_quantity' => 50,
        'category' => 'electronics',
        'status' => 'active',
        'sku' => 'AIRPRO',
    ]);

    Product::create([
        'seller_id' => $seller->id,
        'title' => 'Nike Running Shoes',
        'description' => 'Comfortable running shoes for daily workout',
        'price' => 4999,
        'stock_quantity' => 100,
        'category' => 'sports',
        'status' => 'active',
        'sku' => 'NIKERN',
    ]);

    echo "Created 3 test products for seller1\n";
} else {
    echo "Seller not found\n";
}
