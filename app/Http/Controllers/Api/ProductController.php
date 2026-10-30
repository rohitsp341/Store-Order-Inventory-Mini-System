<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateStockRequest;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function index(): JsonResponse
    {
        $products = Product::orderBy('name')->get();

        return response()->json([
            'success' => true,
            'message' => 'Products retrieved successfully',
            'data' => $products,
        ]);
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = Product::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully',
            'data' => $product,
        ], 201);
    }

    public function updateStock(UpdateStockRequest $request, Product $product): JsonResponse
    {
        $product->update([
            'stock_quantity' => $request->validated('stock_quantity'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Stock quantity updated successfully',
            'data' => $product,
        ]);
    }

    public function lowStock(Request $request): JsonResponse
    {
        $validator = Validator::make($request->query(), [
            'threshold' => ['nullable', 'integer', 'min:0'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'The given data was invalid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $threshold = $request->query('threshold', 5);

        $products = Product::where('stock_quantity', '<', (int) $threshold)
            ->orderBy('stock_quantity', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Low-stock products retrieved successfully',
            'data' => $products,
        ]);
    }
}
