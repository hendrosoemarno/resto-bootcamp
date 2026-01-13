<?php

namespace App\Http\Controllers\Api\Payment;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Simulate Payment Gateway Callback / Webhook
     * In production, this verifies signature from Midtrans/Xendit
     */
    public function callback(Request $request)
    {
        // 1. Validation (Simulated signature check)
        $request->validate([
            'order_number' => 'required|exists:orders,order_number',
            'amount' => 'required|numeric',
            'status' => 'required|in:settlement,paid,success',
            'method' => 'required|string'
        ]);

        $order = Order::where('order_number', $request->order_number)->firstOrFail();

        // 2. Validate Amount
        if ((float) $request->amount != (float) $order->total_amount) {
            return response()->json(['message' => 'Invalid amount'], 400);
        }

        // 3. Process Payment via Service
        $this->orderService->markAsPaid($order, [
            'amount' => $request->input('amount'),
            'method' => $request->input('method'), // e.g., 'QRIS_MOCK'
            'external_id' => 'TX-' . time(),
        ]);

        return response()->json(['message' => 'Payment processed successfully']);
    }

    /**
     * Cashier Manual Confirmation (Auth required strictly)
     * For "Bayar Tunai" scenario
     */
    public function manualConfirm(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'amount' => 'required|numeric',
            'received_amount' => 'required|numeric|min:' . $request->amount,
        ]);

        $order = Order::findOrFail($request->order_id);

        // Ensure cashier/admin
        if (!$request->user()->hasRole('admin') && !$request->user()->hasRole('cashier')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $change = $request->received_amount - $request->amount;

        // Process
        $this->orderService->markAsPaid($order, [
            'amount' => $request->amount,
            'method' => 'CASH',
            'external_id' => null,
            'payment_details' => json_encode([
                'cash_received' => (float) $request->received_amount,
                'change' => (float) $change,
            ])
        ], $request->user()->id);

        return response()->json(['message' => 'Cash payment confirmed']);
    }
}
