<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Http\Request;

class SellerManagementController extends Controller
{
    /**
     * Get all sellers
     */
    public function index(Request $request)
    {
        $query = Seller::with('user');

        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Pagination
        $perPage = $request->query('per_page', 15);
        $sellers = $query->latest()->paginate($perPage);

        // Transform the data
        $sellers->getCollection()->transform(function ($seller) {
            return [
                'id' => $seller->id,
                'user_id' => $seller->user_id,
                'name' => $seller->name,
                'business_name' => $seller->business_name ?: $seller->name,
                'contact_person' => $seller->contact_person ?: $seller->name,
                'email' => $seller->email ?: ($seller->user->email ?? 'N/A'),
                'phone' => $seller->phone ?: 'N/A',
                'business_address' => $seller->business_address,
                'profile_picture' => $seller->profile_picture,
                'status' => $seller->status,
                'created_at' => $seller->created_at->toDateTimeString(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $sellers->items(),
            'meta' => [
                'current_page' => $sellers->currentPage(),
                'per_page' => $sellers->perPage(),
                'total' => $sellers->total(),
                'last_page' => $sellers->lastPage(),
            ],
        ], 200);
    }

    /**
     * Get single seller details
     */
    public function show($id)
    {
        $seller = Seller::with('user')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $seller->id,
                'user_id' => $seller->user_id,
                'name' => $seller->name,
                'business_name' => $seller->business_name ?: $seller->name,
                'contact_person' => $seller->contact_person ?: $seller->name,
                'email' => $seller->email ?: ($seller->user->email ?? 'N/A'),
                'phone' => $seller->phone ?: 'N/A',
                'business_address' => $seller->business_address,
                'profile_picture' => $seller->profile_picture,
                'address' => $seller->address,
                'city' => $seller->city,
                'state' => $seller->state,
                'pincode' => $seller->pincode,
                'gstin' => $seller->gstin,
                'pan' => $seller->pan,
                'status' => $seller->status,
                'created_at' => $seller->created_at->toDateTimeString(),
                'updated_at' => $seller->updated_at->toDateTimeString(),
            ],
        ], 200);
    }

    /**
     * Approve seller
     */
    public function approve($id)
    {
        $seller = Seller::findOrFail($id);
        
        $seller->status = 'active';
        $seller->save();

        return response()->json([
            'success' => true,
            'message' => 'Seller approved successfully',
            'data' => $seller,
        ], 200);
    }

    /**
     * Reject seller
     */
    public function reject($id)
    {
        $seller = Seller::findOrFail($id);
        
        $seller->status = 'inactive';
        $seller->save();

        return response()->json([
            'success' => true,
            'message' => 'Seller rejected',
            'data' => $seller,
        ], 200);
    }

    /**
     * Get products by seller
     */
    public function products($id)
    {
        $seller = Seller::findOrFail($id);
        
        $products = $seller->products()
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->title,
                    'price' => $product->price,
                    'stock' => $product->stock_quantity,
                    'status' => $product->status,
                    'category' => [
                        'name' => $product->category ?? 'N/A',
                    ],
                    'created_at' => $product->created_at->toDateTimeString(),
                ];
            }),
        ], 200);
    }

    /**
     * Delete seller
     */
    public function destroy($id)
    {
        $seller = Seller::findOrFail($id);
        $seller->delete();

        return response()->json([
            'success' => true,
            'message' => 'Seller deleted successfully',
        ], 200);
    }
}
