<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Models\Review;
use Illuminate\Http\JsonResponse;

class ReviewController extends Controller
{
    public function index(): JsonResponse
    {
        $reviews = auth()->user()->reviews()->with('product')->orderBy('created_at', 'desc')->get();
        return response()->json(ReviewResource::collection($reviews));
    }

    public function store(ReviewRequest $request): JsonResponse
    {
        $review = auth()->user()->reviews()->create($request->validated());
        return response()->json(new ReviewResource($review->load('product')), 201);
    }

    public function show(int $id): JsonResponse
    {
        $review = auth()->user()->reviews()->with('product')->find($id);
        if (!$review) {
            return response()->json(['message' => 'Review not found'], 404);
        }
        return response()->json(new ReviewResource($review));
    }

    public function update(ReviewRequest $request, int $id): JsonResponse
    {
        $review = auth()->user()->reviews()->find($id);
        if (!$review) {
            return response()->json(['message' => 'Review not found'], 404);
        }

        $review->update($request->validated());
        return response()->json(new ReviewResource($review->load('product')));
    }

    public function destroy(int $id): JsonResponse
    {
        $review = auth()->user()->reviews()->find($id);
        if (!$review) {
            return response()->json(['message' => 'Review not found'], 404);
        }

        $review->delete();
        return response()->json(['message' => 'Review deleted successfully']);
    }
}