<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing categories
        // Delete all categories (will handle foreign key constraints automatically)
        try {
            // Check if parent_id column exists
            if (Schema::hasColumn('categories', 'parent_id')) {
                // Delete subcategories first, then main categories
                Category::whereNotNull('parent_id')->delete();
                Category::whereNull('parent_id')->delete();
            } else {
                // If parent_id doesn't exist, just delete all
                Category::query()->delete();
            }
        } catch (\Exception $e) {
            // If there are any issues, just delete all categories
            Category::query()->delete();
        }

        // Main Categories
        $clothes = Category::create([
            'name' => 'Clothes',
            'description' => 'Fashion and clothing items for all ages',
            'parent_id' => null,
        ]);

        $gadgets = Category::create([
            'name' => 'Gadgets',
            'description' => 'Electronic devices and accessories',
            'parent_id' => null,
        ]);

        $mobile = Category::create([
            'name' => 'Mobile Phones',
            'description' => 'Smartphones and mobile devices',
            'parent_id' => null,
        ]);

        $home = Category::create([
            'name' => 'Home & Living',
            'description' => 'Home decor, furniture, and living essentials',
            'parent_id' => null,
        ]);

        $sports = Category::create([
            'name' => 'Sports & Fitness',
            'description' => 'Sports equipment and fitness gear',
            'parent_id' => null,
        ]);

        $books = Category::create([
            'name' => 'Books & Media',
            'description' => 'Books, magazines, and digital media',
            'parent_id' => null,
        ]);

        $beauty = Category::create([
            'name' => 'Beauty & Personal Care',
            'description' => 'Cosmetics, skincare, and personal care products',
            'parent_id' => null,
        ]);

        $toys = Category::create([
            'name' => 'Toys & Games',
            'description' => 'Toys, games, and entertainment for kids',
            'parent_id' => null,
        ]);

        // Subcategories for Clothes
        Category::create([
            'name' => "Men's Clothing",
            'description' => 'Clothing for men',
            'parent_id' => $clothes->id,
        ]);

        Category::create([
            'name' => "Women's Clothing",
            'description' => 'Clothing for women',
            'parent_id' => $clothes->id,
        ]);

        Category::create([
            'name' => "Kids' Clothing",
            'description' => 'Clothing for children',
            'parent_id' => $clothes->id,
        ]);

        Category::create([
            'name' => 'Accessories',
            'description' => 'Fashion accessories like bags, belts, watches',
            'parent_id' => $clothes->id,
        ]);

        // Subcategories for Gadgets
        Category::create([
            'name' => 'Laptops',
            'description' => 'Laptop computers and notebooks',
            'parent_id' => $gadgets->id,
        ]);

        Category::create([
            'name' => 'Tablets',
            'description' => 'Tablet devices',
            'parent_id' => $gadgets->id,
        ]);

        Category::create([
            'name' => 'Wearables',
            'description' => 'Smartwatches, fitness trackers, and wearables',
            'parent_id' => $gadgets->id,
        ]);

        Category::create([
            'name' => 'Audio Devices',
            'description' => 'Headphones, speakers, and audio equipment',
            'parent_id' => $gadgets->id,
        ]);

        Category::create([
            'name' => 'Gaming Accessories',
            'description' => 'Gaming controllers, keyboards, mice, and accessories',
            'parent_id' => $gadgets->id,
        ]);

        // Subcategories for Mobile Phones
        Category::create([
            'name' => 'Android Phones',
            'description' => 'Android smartphones',
            'parent_id' => $mobile->id,
        ]);

        Category::create([
            'name' => 'iOS Phones',
            'description' => 'iPhone and iOS devices',
            'parent_id' => $mobile->id,
        ]);

        Category::create([
            'name' => 'Mobile Accessories',
            'description' => 'Cases, chargers, cables, and mobile phone accessories',
            'parent_id' => $mobile->id,
        ]);

        // Subcategories for Home & Living
        Category::create([
            'name' => 'Furniture',
            'description' => 'Home furniture and decor',
            'parent_id' => $home->id,
        ]);

        Category::create([
            'name' => 'Kitchen & Dining',
            'description' => 'Kitchen appliances and dining essentials',
            'parent_id' => $home->id,
        ]);

        Category::create([
            'name' => 'Home Decor',
            'description' => 'Decorative items for home',
            'parent_id' => $home->id,
        ]);

        // Subcategories for Sports & Fitness
        Category::create([
            'name' => 'Fitness Equipment',
            'description' => 'Exercise equipment and fitness gear',
            'parent_id' => $sports->id,
        ]);

        Category::create([
            'name' => 'Outdoor Sports',
            'description' => 'Outdoor sports equipment',
            'parent_id' => $sports->id,
        ]);

        Category::create([
            'name' => 'Sports Apparel',
            'description' => 'Sportswear and athletic clothing',
            'parent_id' => $sports->id,
        ]);

        // Subcategories for Books & Media
        Category::create([
            'name' => 'Books',
            'description' => 'Physical and digital books',
            'parent_id' => $books->id,
        ]);

        Category::create([
            'name' => 'E-Books',
            'description' => 'Digital books and e-readers',
            'parent_id' => $books->id,
        ]);

        // Subcategories for Beauty & Personal Care
        Category::create([
            'name' => 'Skincare',
            'description' => 'Skincare products and treatments',
            'parent_id' => $beauty->id,
        ]);

        Category::create([
            'name' => 'Makeup',
            'description' => 'Cosmetics and makeup products',
            'parent_id' => $beauty->id,
        ]);

        Category::create([
            'name' => 'Hair Care',
            'description' => 'Hair care products and accessories',
            'parent_id' => $beauty->id,
        ]);

        // Subcategories for Toys & Games
        Category::create([
            'name' => 'Action Figures',
            'description' => 'Action figures and collectibles',
            'parent_id' => $toys->id,
        ]);

        Category::create([
            'name' => 'Board Games',
            'description' => 'Board games and puzzles',
            'parent_id' => $toys->id,
        ]);

        Category::create([
            'name' => 'Educational Toys',
            'description' => 'Educational and learning toys',
            'parent_id' => $toys->id,
        ]);

        $this->command->info('✅ Categories seeded successfully!');
        $this->command->info('   - 8 main categories created');
        $this->command->info('   - 25 subcategories created');
    }
}

