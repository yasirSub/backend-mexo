<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Try to get authenticated seller (could be from different guards)
        $seller = $request->user('sanctum') ?? $request->user();
        $sellerId = $seller ? $seller->id : null;
        
        // Debug: Log seller info
        Log::info('Category index - Seller ID: ' . ($sellerId ?? 'null'));
        Log::info('Category index - Request user: ' . ($request->user() ? 'exists' : 'null'));
        
        // Test query to see products
        if ($sellerId) {
            $testProducts = \App\Models\Product::where('seller_id', $sellerId)
                ->whereNotNull('category_id')
                ->get(['id', 'title', 'category_id']);
            Log::info('Category index - Products with category_id: ' . $testProducts->count());
            foreach ($testProducts as $p) {
                Log::info("Product: {$p->id} - {$p->title} - Category: {$p->category_id}");
            }
        }

        $query = Category::with(['subcategories', 'parent']);

        // Filter by parent_id (null for main categories, id for subcategories)
        if ($request->has('parent_id')) {
            if ($request->parent_id === 'null' || $request->parent_id === '') {
                $query->whereNull('parent_id');
            } else {
                $query->where('parent_id', $request->parent_id);
            }
        }

        // Search by name
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Include subcategories count and products count (filtered by seller if authenticated)
        $query->withCount([
            'subcategories',
            'products' => function ($q) use ($sellerId) {
                if ($sellerId) {
                    $q->where('seller_id', $sellerId);
                }
            }
        ]);

        // Eager load subcategories with their products and counts
        $query->with(['subcategories' => function ($q) use ($sellerId) {
            $q->withCount([
                'subcategories',
                'products' => function ($subQ) use ($sellerId) {
                    if ($sellerId) {
                        $subQ->where('seller_id', $sellerId);
                    }
                }
            ]);
        }]);

        // Order by name
        $categories = $query->orderBy('name')->get();

        // Manually set product counts for authenticated sellers
        if ($sellerId) {
            foreach ($categories as $category) {
                // Get all subcategory IDs for this category
                $subcategoryIds = $category->subcategories->pluck('id')->toArray();
                
                // Count products directly in this category
                $directProducts = \App\Models\Product::where('seller_id', $sellerId)
                    ->where('category_id', $category->id)
                    ->count();
                
                // Count products in subcategories
                $subcategoryProducts = 0;
                if (!empty($subcategoryIds)) {
                    $subcategoryProducts = \App\Models\Product::where('seller_id', $sellerId)
                        ->whereIn('category_id', $subcategoryIds)
                        ->count();
                }
                
                // Total products = direct products + subcategory products
                $category->products_count = $directProducts + $subcategoryProducts;
                
                // Also update subcategories
                foreach ($category->subcategories as $subcategory) {
                    $subcategory->products_count = \App\Models\Product::where('seller_id', $sellerId)
                        ->where('category_id', $subcategory->id)
                        ->count();
                    
                    // Also update subcategory count
                    $subcategory->subcategories_count = \App\Models\Category::where('parent_id', $subcategory->id)->count();
                }
                
                Log::info("Category: {$category->name} - Direct: {$directProducts}, Subcategory: {$subcategoryProducts}, Total: {$category->products_count}");
            }
        }

        // Load products relationship for main categories and subcategories to calculate out of stock count
        if ($sellerId) {
            $categories->load(['products' => function ($q) use ($sellerId) {
                $q->where('seller_id', $sellerId);
            }]);
            $categories->each(function ($category) use ($sellerId) {
                $category->subcategories->load(['products' => function ($q) use ($sellerId) {
                    $q->where('seller_id', $sellerId);
                }]);
            });
        } else {
            $categories->load('products');
            $categories->each(function ($category) {
                $category->subcategories->load('products');
            });
        }

        return response()->json([
            'success' => true,
            'data' => CategoryResource::collection($categories),
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $category = Category::with(['subcategories', 'parent', 'products'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => new CategoryResource($category),
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
