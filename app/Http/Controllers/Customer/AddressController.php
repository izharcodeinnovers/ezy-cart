<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddressRequest;
use App\Http\Resources\AddressResource;
use App\Models\Address;
use Illuminate\Http\JsonResponse;

class AddressController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Address::class, 'address');
    }

    public function index(): JsonResponse
    {
        $addresses = auth()->user()->addresses()->orderBy('is_default', 'desc')->get();
        return response()->json(AddressResource::collection($addresses));
    }

    public function store(AddressRequest $request): JsonResponse
    {
        $data = $request->validated();

        // If this is the first address or is_default is true, unset other defaults
        if ($data['is_default'] ?? false) {
            auth()->user()->addresses()->where('type', $data['type'])->update(['is_default' => false]);
        } elseif (auth()->user()->addresses()->where('type', $data['type'])->count() === 0) {
            $data['is_default'] = true;
        }

        $address = auth()->user()->addresses()->create($data);
        return response()->json(new AddressResource($address), 201);
    }

    public function show(int $id): JsonResponse
    {
        $address = auth()->user()->addresses()->find($id);
        if (!$address) {
            return response()->json(['message' => 'Address not found'], 404);
        }
        return response()->json(new AddressResource($address));
    }

    public function update(AddressRequest $request, int $id): JsonResponse
    {
        $address = auth()->user()->addresses()->find($id);
        if (!$address) {
            return response()->json(['message' => 'Address not found'], 404);
        }

        $data = $request->validated();

        if ($data['is_default'] ?? false) {
            auth()->user()->addresses()->where('type', $address->type)->where('id', '!=', $id)->update(['is_default' => false]);
        }

        $address->update($data);
        return response()->json(new AddressResource($address));
    }

    public function destroy(int $id): JsonResponse
    {
        $address = auth()->user()->addresses()->find($id);
        if (!$address) {
            return response()->json(['message' => 'Address not found'], 404);
        }

        $wasDefault = $address->is_default;
        $type = $address->type;
        $address->delete();

        if ($wasDefault) {
            $newDefault = auth()->user()->addresses()->where('type', $type)->first();
            if ($newDefault) {
                $newDefault->update(['is_default' => true]);
            }
        }

        return response()->json(['message' => 'Address deleted successfully']);
    }

    public function setDefault(int $id): JsonResponse
    {
        $address = auth()->user()->addresses()->find($id);
        if (!$address) {
            return response()->json(['message' => 'Address not found'], 404);
        }

        auth()->user()->addresses()->where('type', $address->type)->update(['is_default' => false]);
        $address->update(['is_default' => true]);

        return response()->json(new AddressResource($address));
    }
}