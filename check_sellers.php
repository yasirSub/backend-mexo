<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$sellers = App\Models\Seller::select('id', 'name', 'business_name', 'contact_person', 'email', 'phone', 'status', 'is_active', 'created_at')->get();

echo "Total Sellers: " . $sellers->count() . PHP_EOL . PHP_EOL;

foreach($sellers as $seller) {
    echo "ID: {$seller->id}" . PHP_EOL;
    echo "Name: {$seller->name}" . PHP_EOL;
    echo "Business Name: {$seller->business_name}" . PHP_EOL;
    echo "Contact Person: {$seller->contact_person}" . PHP_EOL;
    echo "Email: {$seller->email}" . PHP_EOL;
    echo "Phone: {$seller->phone}" . PHP_EOL;
    echo "Status: {$seller->status}" . PHP_EOL;
    echo "Active: " . ($seller->is_active ? 'Yes' : 'No') . PHP_EOL;
    echo "Created: {$seller->created_at}" . PHP_EOL;
    echo "---" . PHP_EOL;
}
