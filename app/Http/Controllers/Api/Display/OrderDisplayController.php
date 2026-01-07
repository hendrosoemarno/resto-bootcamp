<?php

namespace App\Http\Controllers\Api\Display;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderDisplayController extends Controller
{
    /**
     * Public Endpoint for Queue Display Screen
     * Shows orders that are COOKING (Preparing) and READY (To Pick Up)
     */
    public function index(Request $request, $restaurantId)
    {
        // Get active orders for display
        $orders = Order::where('restaurant_id', $restaurantId)
            ->whereIn('status', ['COOKING', 'READY'])
            ->orderBy('updated_at', 'desc') // Show latest first
            ->get()
            ->map(function ($order) {
                return [
                    'order_number' => $order->order_number,
                    'status' => $order->status, // COOKING or READY
                    'customer_name' => $order->customer_name, // Optional privacy concern? Maybe mask it later
                    'updated_at' => $order->updated_at,
                ];
            });

        return response()->json([
            'ready' => $orders->where('status', 'READY')->values(),
            'preparing' => $orders->where('status', 'COOKING')->values(),
        ]);
    }
}
