<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Get seller_id from request if available
        $sellerId = $request->user()?->id;
        
        // Calculate out of stock products count (filtered by seller if available)
        $productsQuery = $this->products();
        if ($sellerId) {
            $productsQuery->where('seller_id', $sellerId);
        }
        $outOfStockCount = $productsQuery->where('stock_quantity', '<=', 0)->count();
        
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'parent_id' => $this->parent_id,
            'image' => $this->image,
            'products_count' => $this->whenCounted('products'),
            'out_of_stock_count' => $outOfStockCount,
            'subcategories_count' => $this->whenCounted('subcategories'),
            'parent' => $this->whenLoaded('parent'),
            'subcategories' => CategoryResource::collection($this->whenLoaded('subcategories')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
