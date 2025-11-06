<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductManagementController extends Controller
{
    /**
     * Get all products
     */
    public function index(Request $request)
    {
        $query = Product::with('seller');

        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by seller if provided
        if ($request->has('seller_id')) {
            $query->where('seller_id', $request->seller_id);
        }

        // Search by title
        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        // Pagination
        $perPage = $request->query('per_page', 15);
        $products = $query->latest()->paginate($perPage);

        // Transform the data
        $products->getCollection()->transform(function ($product) {
            try {
                // Handle images - it's a JSON column, not a relationship
                $images = $product->images;
                $image = null;
                if (is_array($images) && count($images) > 0) {
                    $image = $images[0];
                } elseif (is_string($images) && !empty($images)) {
                    $image = $images;
                }
                
                // Handle category - use column value directly
                $categoryName = $product->getAttribute('category') ?? 'N/A';
                if (empty($categoryName) || $categoryName === null) {
                    $categoryName = 'N/A';
                }
                
                // Handle seller
                $sellerName = 'N/A';
                try {
                    if ($product->relationLoaded('seller') && $product->seller) {
                        $sellerName = $product->seller->business_name ?? 'N/A';
                    }
                } catch (\Exception $e) {
                    // Seller handling failed, use default
                    $sellerName = 'N/A';
                }
                
                return [
                    'id' => $product->id,
                    'seller_id' => $product->seller_id,
                    'seller_name' => $sellerName,
                    'title' => $product->title ?? 'N/A',
                    'description' => $product->description ?? '',
                    'price' => $product->price ?? 0,
                    'stock_quantity' => $product->stock_quantity ?? 0,
                    'status' => $product->status ?? 'pending',
                    'image' => $image,
                    'images' => is_array($images) ? $images : ($images ? [$images] : []),
                    'sku' => $product->sku ?? null,
                    'category_id' => $product->category_id ?? null,
                    'category' => $categoryName,
                    'created_at' => $product->created_at ? $product->created_at->toDateTimeString() : now()->toDateTimeString(),
                ];
            } catch (\Exception $e) {
                // Log error and return minimal data
                \Log::error('Error transforming product: ' . $e->getMessage(), [
                    'product_id' => $product->id ?? null,
                    'error' => $e->getTraceAsString()
                ]);
                
                return [
                    'id' => $product->id ?? 0,
                    'seller_id' => $product->seller_id ?? 0,
                    'seller_name' => 'N/A',
                    'title' => $product->title ?? 'N/A',
                    'description' => '',
                    'price' => 0,
                    'stock_quantity' => 0,
                    'status' => 'pending',
                    'image' => null,
                    'images' => [],
                    'sku' => null,
                    'category_id' => null,
                    'category' => 'N/A',
                    'created_at' => now()->toDateTimeString(),
                ];
            }
        });

        return response()->json([
            'success' => true,
            'data' => $products->items(),
            'meta' => [
                'current_page' => $products->currentPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
                'last_page' => $products->lastPage(),
            ],
        ], 200);
    }

    /**
     * Get single product details
     */
    public function show($id)
    {
        try {
            $product = Product::with('seller')->findOrFail($id);
            
            // Handle images - it's a JSON column, not a relationship
            $images = $product->images;
            $image = null;
            if (is_array($images) && count($images) > 0) {
                $image = $images[0];
            } elseif (is_string($images) && !empty($images)) {
                $image = $images;
            }
            
            // Handle category - try to load relationship if category_id exists
            $categoryData = null;
            if ($product->category_id) {
                try {
                    $category = \App\Models\Category::find($product->category_id);
                    if ($category) {
                        $categoryData = [
                            'id' => $category->id,
                            'name' => $category->name,
                            'description' => $category->description ?? null,
                        ];
                    }
                } catch (\Exception $e) {
                    // Category not found or error loading it
                    $categoryData = null;
                }
            }
            
            // If category relationship failed, try using category column
            if (!$categoryData && $product->getAttribute('category')) {
                $categoryColumn = $product->getAttribute('category');
                if (is_string($categoryColumn) && !empty($categoryColumn)) {
                    $categoryData = [
                        'id' => $product->category_id,
                        'name' => $categoryColumn,
                        'description' => null,
                    ];
                }
            }
            
            // Format the response
            $formattedProduct = [
                'id' => $product->id,
                'seller_id' => $product->seller_id,
                'title' => $product->title ?? 'N/A',
                'name' => $product->title ?? 'N/A',
                'description' => $product->description ?? 'No description provided.',
                'price' => $product->price ?? 0,
                'stock_quantity' => $product->stock_quantity ?? 0,
                'stock' => $product->stock_quantity ?? 0,
                'status' => $product->status ?? 'pending',
                'sku' => $product->sku ?? 'N/A',
                'category_id' => $product->category_id ?? null,
                'category' => $categoryData,
                'image' => $image,
                'images' => is_array($images) ? $images : ($images ? [$images] : []),
                'created_at' => $product->created_at ? $product->created_at->toDateTimeString() : now()->toDateTimeString(),
                'updated_at' => $product->updated_at ? $product->updated_at->toDateTimeString() : now()->toDateTimeString(),
            ];

            return response()->json([
                'success' => true,
                'data' => $formattedProduct,
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error fetching product details: ' . $e->getMessage(), [
                'product_id' => $id,
                'error' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Product not found or error loading product details',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Approve product
     */
    public function approve($id)
    {
        $product = Product::findOrFail($id);
        
        $product->status = 'active';
        $product->save();

        return response()->json([
            'success' => true,
            'message' => 'Product approved successfully',
            'data' => $product,
        ], 200);
    }

    /**
     * Reject product
     */
    public function reject($id)
    {
        $product = Product::findOrFail($id);
        
        $product->status = 'inactive';
        $product->save();

        return response()->json([
            'success' => true,
            'message' => 'Product rejected',
            'data' => $product,
        ], 200);
    }

    /**
     * Delete product
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully',
        ], 200);
    }

    /**
     * Update product status
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:active,inactive,pending',
        ]);

        $product = Product::findOrFail($id);
        $product->status = $request->status;
        $product->save();

        return response()->json([
            'success' => true,
            'message' => 'Product status updated successfully',
            'data' => $product,
        ], 200);
    }
}
