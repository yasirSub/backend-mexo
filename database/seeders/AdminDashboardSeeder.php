<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Seller;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminDashboardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Admin
        Admin::updateOrCreate(
            ['email' => 'admin@mexo.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
                'is_active' => true,
            ]
        );

        $this->command->info('Admin created: admin@mexo.com / password');

        // Create Users (Customers)
        $users = [];
        for ($i = 1; $i <= 5; $i++) {
            $users[] = User::firstOrCreate(
                ['email' => "customer{$i}@example.com"],
                [
                    'name' => "Customer {$i}",
                    'password' => Hash::make('password'),
                ]
            );
        }

        $this->command->info('Created 5 customers');

        // Create Sellers
        $sellers = [];
        $statuses = ['active', 'pending', 'inactive'];
        for ($i = 1; $i <= 10; $i++) {
            $seller = Seller::firstOrCreate(
                ['email' => "seller{$i}@example.com"],
                [
                    'name' => "Seller {$i}",
                    'email' => "seller{$i}@example.com",
                    'password' => Hash::make('password'),
                    'business_name' => "Business {$i}",
                    'contact_person' => "Contact Person {$i}",
                    'phone' => "98765432" . str_pad($i, 2, '0', STR_PAD_LEFT),
                    'address' => "{$i} Main Street",
                    'city' => 'Mumbai',
                    'state' => 'Maharashtra',
                    'pincode' => '400001',
                    'gstin' => '27AABCU9603R1ZM',
                    'pan' => 'AABCU9603R',
                    'status' => $statuses[$i % 3],
                    'is_active' => true,
                ]
            );
            $sellers[] = $seller;
        }

        $this->command->info('Created 10 sellers');

        // Create Products
        $products = [];
        $categories = ['Electronics', 'Fashion', 'Home & Kitchen', 'Sports', 'Books'];
        $statuses = ['active', 'pending', 'inactive'];
        
        foreach ($sellers as $seller) {
            for ($i = 1; $i <= 5; $i++) {
                $product = Product::firstOrCreate(
                    [
                        'seller_id' => $seller->id,
                        'title' => "Product {$i} by {$seller->business_name}",
                    ],
                    [
                        'description' => "This is a great product from {$seller->business_name}",
                        'price' => rand(100, 5000),
                        'category' => $categories[array_rand($categories)],
                        'stock_quantity' => rand(0, 100),
                        'sku' => 'SKU' . str_pad(($seller->id * 100 + $i), 6, '0', STR_PAD_LEFT),
                        'status' => $statuses[$i % 3],
                    ]
                );
                $products[] = $product;
            }
        }

        $this->command->info('Created 50 products');

        // Create Orders
        $orderStatuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];
        $paymentMethods = ['cod', 'online', 'upi'];
        
        for ($i = 1; $i <= 30; $i++) {
            $seller = $sellers[array_rand($sellers)];
            $user = $users[array_rand($users)];
            $status = $orderStatuses[array_rand($orderStatuses)];
            
            $order = Order::firstOrCreate(
                ['order_number' => 'ORD' . str_pad($i, 8, '0', STR_PAD_LEFT)],
                [
                    'seller_id' => $seller->id,
                    'user_id' => $user->id,
                    'total_amount' => rand(500, 10000),
                    'status' => $status,
                    'payment_status' => in_array($status, ['delivered', 'completed']) ? 'paid' : 'pending',
                    'payment_method' => $paymentMethods[array_rand($paymentMethods)],
                    'payout_status' => $status === 'delivered' ? 'pending' : 'not_applicable',
                    'shipping_address' => [
                        'name' => $user->name,
                        'phone' => '9876543210',
                        'address' => '123 Street',
                        'city' => 'Mumbai',
                        'state' => 'Maharashtra',
                        'pincode' => '400001',
                    ],
                ]
            );

            // Create order items
            $numItems = rand(1, 3);
            $availableProducts = Product::where('seller_id', $seller->id)->inRandomOrder()->limit($numItems)->get();
            
            foreach ($availableProducts as $product) {
                $quantity = rand(1, 3);
                OrderItem::firstOrCreate(
                    [
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                    ],
                    [
                        'quantity' => $quantity,
                        'price' => $product->price,
                        'total' => $product->price * $quantity,
                    ]
                );
            }
        }

        $this->command->info('Created 30 orders with items');
        $this->command->info('Seeding completed successfully!');
    }
}
