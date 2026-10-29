<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CustomerOrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->query(), [
            'email' => ['required', 'string', 'email'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'The given data was invalid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $email = $request->query('email');
        $customer = Customer::where('email', $email)
            ->with([
                'orders' => function ($query) {
                    $query->latest();
                },
                'orders.orderItems.product',
            ])
            ->first();

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found',
                'errors' => [
                    'email' => ['No customer found with the provided email address.'],
                ],
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Customer orders retrieved successfully',
            'data' => [
                'customer' => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'email' => $customer->email,
                ],
                'orders' => $customer->orders,
            ],
        ]);
    }
}
