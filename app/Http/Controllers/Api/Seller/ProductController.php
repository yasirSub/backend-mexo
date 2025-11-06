<?php

namespace App\Http\Controllers\Api\Seller;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        try {
            $seller = $request->user();
            
            $products = Product::with('category')
                ->where('seller_id', $seller->id)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($product) {
                    // Get category relationship (not the string column)
                    $categoryRelation = $product->getRelation('category');
                    
                    return [
                        'id' => $product->id,
                        'name' => $product->title ?? $product->name ?? 'Unnamed Product',
                        'description' => $product->description,
                        'price' => $product->price ?? 0,
                        'stock' => $product->stock_quantity ?? $product->stock ?? 0,
                        'status' => $product->status ?? 'active',
                        'category_id' => $product->category_id,
                        'category' => ($categoryRelation && is_object($categoryRelation)) ? [
                            'id' => $categoryRelation->id,
                            'name' => $categoryRelation->name,
                            'description' => $categoryRelation->description,
                        ] : null,
                        'sku' => $product->sku ?? 'N/A',
                        'image' => $product->images ? (is_array($product->images) ? ($product->images[0] ?? null) : null) : null,
                        'created_at' => $product->created_at ? $product->created_at->toISOString() : now()->toISOString(),
                        'updated_at' => $product->updated_at ? $product->updated_at->toISOString() : now()->toISOString(),
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $products,
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading products: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load products: ' . $e->getMessage(),
                'data' => [],
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'images' => 'nullable|array',
            'images.*' => 'nullable|string',
        ]);

        $seller = $request->user();

        Log::info('Creating product - Seller ID: ' . $seller->id);
        Log::info('Creating product - Category ID: ' . ($validated['category_id'] ?? 'null'));

        $product = Product::create([
            'seller_id' => $seller->id,
            'title' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'stock_quantity' => $validated['stock'],
            'status' => 'active',
            'category_id' => $validated['category_id'] ?? null,
            'category' => 'general',
            'sku' => 'PRD-' . strtoupper(uniqid()),
            'images' => $validated['images'] ?? null,
        ]);

        Log::info('Product created - ID: ' . $product->id . ', Category ID: ' . $product->category_id);

        // Reload product with relationships
        $product->load('category');
        
        // Get category relationship (not the string column)
        $categoryRelation = $product->getRelation('category');
        
        return response()->json([
            'success' => true,
            'message' => 'Product created successfully',
            'data' => [
                'id' => $product->id,
                'name' => $product->title,
                'description' => $product->description,
                'price' => $product->price,
                'stock' => $product->stock_quantity,
                'status' => $product->status,
                'category_id' => $product->category_id,
                'category' => ($categoryRelation && is_object($categoryRelation)) ? [
                    'id' => $categoryRelation->id,
                    'name' => $categoryRelation->name,
                    'description' => $categoryRelation->description,
                ] : null,
                'sku' => $product->sku,
                'image' => $product->images ? (is_array($product->images) ? ($product->images[0] ?? null) : null) : null,
                'images' => $product->images,
                'created_at' => $product->created_at ? $product->created_at->toISOString() : now()->toISOString(),
                'updated_at' => $product->updated_at ? $product->updated_at->toISOString() : now()->toISOString(),
            ],
        ], 201);
    }

    public function show(Request $request, $id)
    {
        $seller = $request->user();
        
        $product = Product::with('category')
            ->where('seller_id', $seller->id)
            ->where('id', $id)
            ->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404);
        }

        // Get category relationship (not the string column)
        $categoryRelation = $product->getRelation('category');
        
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $product->id,
                'name' => $product->title,
                'description' => $product->description,
                'price' => $product->price,
                'stock' => $product->stock_quantity,
                'status' => $product->status,
                'category_id' => $product->category_id,
                'category' => ($categoryRelation && is_object($categoryRelation)) ? [
                    'id' => $categoryRelation->id,
                    'name' => $categoryRelation->name,
                    'description' => $categoryRelation->description,
                ] : null,
                'sku' => $product->sku,
                'image' => $product->images ? (is_array($product->images) ? ($product->images[0] ?? null) : null) : null,
                'created_at' => $product->created_at->toISOString(),
                'updated_at' => $product->updated_at->toISOString(),
            ],
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'stock' => 'sometimes|integer|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'images' => 'nullable|array',
            'images.*' => 'nullable|string',
        ]);

        $seller = $request->user();
        
        $product = Product::where('seller_id', $seller->id)
            ->where('id', $id)
            ->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404);
        }

        if (isset($validated['name'])) {
            $product->title = $validated['name'];
        }
        if (isset($validated['description'])) {
            $product->description = $validated['description'];
        }
        if (isset($validated['price'])) {
            $product->price = $validated['price'];
        }
        if (isset($validated['stock'])) {
            $product->stock_quantity = $validated['stock'];
        }
        if (isset($validated['category_id'])) {
            $product->category_id = $validated['category_id'];
        }
        if (isset($validated['images'])) {
            $product->images = $validated['images'];
        }

        $product->save();

        // Reload product with relationships
        $product->refresh();
        $product->load('category');
        
        // Get category relationship (not the string column)
        $categoryRelation = $product->getRelation('category');
        
        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully',
            'data' => [
                'id' => $product->id,
                'name' => $product->title,
                'description' => $product->description,
                'price' => $product->price,
                'stock' => $product->stock_quantity,
                'status' => $product->status,
                'category_id' => $product->category_id,
                'category' => ($categoryRelation && is_object($categoryRelation)) ? [
                    'id' => $categoryRelation->id,
                    'name' => $categoryRelation->name,
                    'description' => $categoryRelation->description,
                ] : null,
                'sku' => $product->sku,
                'image' => $product->images ? (is_array($product->images) ? ($product->images[0] ?? null) : null) : null,
                'images' => $product->images,
                'created_at' => $product->created_at ? $product->created_at->toISOString() : now()->toISOString(),
                'updated_at' => $product->updated_at ? $product->updated_at->toISOString() : now()->toISOString(),
            ],
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $seller = $request->user();
        
        $product = Product::where('seller_id', $seller->id)
            ->where('id', $id)
            ->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404);
        }

        $product->delete(); // Soft delete

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully',
        ]);
    }
}
