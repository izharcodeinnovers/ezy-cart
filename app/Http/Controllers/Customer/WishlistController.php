<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Resources\WishlistResource;
use App\Models\Wishlist;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class WishlistController extends Controller
{
    public function index(): JsonResponse
    {
        $wishlists = auth()->user()->wishlists()->with('product.images')->get();
        return response()->json(WishlistResource::collection($wishlists));
    }

    public function store(int $productId): JsonResponse
    {
        $product = Product::findOrFail($productId);

        $wishlist = Wishlist::firstOrCreate([
            'user_id' => auth()->id(),
            'product_id' => $productId,
        ]);

        return response()->json(new WishlistResource($wishlist->load('product')), $wishlist->wasRecentlyCreated ? 201 : 200);
    }

    public function destroy(int $productId): JsonResponse
    {
        $deleted = Wishlist::where('user_id', auth()->id())
            ->where('product_id', $productId)
            ->delete();

        if (!$deleted) {
            return response()->json(['message' => 'Item not in wishlist'], 404);
        }

        return response()->json(['message' => 'Removed from wishlist']);
    }

    public function toggle(int $productId): JsonResponse
    {
        $product = Product::findOrFail($productId);

        $wishlist = Wishlist::where('user_id', auth()->id())
            ->where('product_id', $productId)
            ->first();

        if ($wishlist) {
            $wishlist->delete();
            return response()->json(['in_wishlist' => false, 'message' => 'Removed from wishlist']);
        }

        $wishlist = Wishlist::create([
            'user_id' => auth()->id(),
            'product_id' => $productId,
        ]);

        return response()->json(['in_wishlist' => true, 'wishlist' => new WishlistResource($wishlist->load('product'))]);
    }
}