<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$products = App\Models\Product::with('seller')->get();

echo "Total Products: " . $products->count() . PHP_EOL . PHP_EOL;

if ($products->count() > 0) {
    foreach($products->take(5) as $product) {
        echo "ID: {$product->id}" . PHP_EOL;
        echo "Title: {$product->title}" . PHP_EOL;
        echo "Seller: {$product->seller->name}" . PHP_EOL;
        echo "Seller ID: {$product->seller_id}" . PHP_EOL;
        echo "Category: {$product->category}" . PHP_EOL;
        echo "Price: {$product->price}" . PHP_EOL;
        echo "Stock: {$product->stock_quantity}" . PHP_EOL;
        echo "Status: {$product->status}" . PHP_EOL;
        echo "---" . PHP_EOL;
    }
} else {
    echo "No products found." . PHP_EOL;
}
