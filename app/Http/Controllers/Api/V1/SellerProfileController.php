<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\SellerResource;
use App\Models\Seller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SellerProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
    public function show(Request $request, $id = null)
    {
        $seller = $request->user() ?? null;
        if ($id && !$seller) {
            $seller = Seller::find($id);
        }

        if (!$seller) {
            return response()->json(['success' => false, 'message' => 'Seller not found'], 404);
        }

        return response()->json(['success' => true, 'data' => new SellerResource($seller)], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, $id = null)
    {
        // Use authenticated user or optional id parameter
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id = null)
    {
        $seller = $request->user() ?? null;
        if ($id && !$seller) {
            $seller = Seller::find($id);
        }

        if (!$seller) {
            return response()->json(['success' => false, 'message' => 'Seller not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'business_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'business_address' => 'nullable|string',
            'profile_picture' => 'nullable|string', // URL to profile picture
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        $seller->name = $request->input('name');
        $seller->phone = $request->input('phone');
        $seller->business_name = $request->input('business_name');
        $seller->business_address = $request->input('business_address');
        
        // Update profile picture if provided
        if ($request->has('profile_picture')) {
            $seller->profile_picture = $request->input('profile_picture');
        }
        
        $seller->save();

        return response()->json(['success' => true, 'message' => 'Profile updated', 'data' => $seller], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id = null)
    {
        // Use authenticated user or optional id parameter
    }
}
