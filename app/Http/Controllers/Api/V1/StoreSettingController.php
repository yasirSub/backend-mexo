<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\StoreSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StoreSettingController extends Controller
{
    public function show(Request $request)
    {
        $seller = $request->user();
        if (!$seller) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $settings = StoreSetting::where('seller_id', $seller->id)->first();
        if (!$settings) {
            // return defaults
            return response()->json(['success' => true, 'data' => [
                'pickup_enabled' => false,
                'min_order_amount' => 0,
                'shipping_policy' => null,
                'support_email' => null,
                'contact_phone' => null,
                'opening_hours' => null,
                'auto_accept_orders' => false,
                'delivery_radius_km' => null,
            ]], 200);
        }

        return response()->json(['success' => true, 'data' => $settings], 200);
    }

    public function update(Request $request)
    {
        $seller = $request->user();
        if (!$seller) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $rules = [
            'pickup_enabled' => 'nullable|boolean',
            'min_order_amount' => 'nullable|numeric',
            'shipping_policy' => 'nullable|string',
            'support_email' => 'nullable|email',
            'contact_phone' => 'nullable|string',
            'opening_hours' => 'nullable|array',
            'auto_accept_orders' => 'nullable|boolean',
            'delivery_radius_km' => 'nullable|numeric',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        // Ensure opening_hours is stored as JSON if present
        if (isset($data['opening_hours']) && is_array($data['opening_hours'])) {
            $data['opening_hours'] = json_encode($data['opening_hours']);
        }

        $settings = StoreSetting::updateOrCreate(
            ['seller_id' => $seller->id],
            $data
        );

        return response()->json(['success' => true, 'message' => 'Store settings saved', 'data' => $settings], 200);
    }
}
