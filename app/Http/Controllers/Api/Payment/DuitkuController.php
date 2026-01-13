<?php

namespace App\Http\Controllers\Api\Payment;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Services\DuitkuService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DuitkuController extends Controller
{
    protected $duitkuService;
    protected $orderService;

    public function __construct(DuitkuService $duitkuService, \App\Services\OrderService $orderService)
    {
        $this->duitkuService = $duitkuService;
        $this->orderService = $orderService;
    }

    /**
     * Create payment request
     */
    public function createPayment(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id'
        ]);

        try {
            $order = Order::with(['items.menu', 'restaurant'])->findOrFail($request->order_id);

            // Check if order is already paid
            if ($order->payment_status === 'PAID') {
                return response()->json([
                    'success' => false,
                    'message' => 'Order sudah dibayar'
                ], 400);
            }

            // Create transaction with Duitku
            $result = $this->duitkuService->createTransaction($order);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                    'error' => $result['error'] ?? null
                ], 500);
            }

            $duitkuData = $result['data'];

            // Save payment record
            $payment = Payment::create([
                'order_id' => $order->id,
                'amount' => $order->total_amount,
                'method' => 'DUITKU',
                'transaction_status' => 'PENDING',
                'transaction_id' => $duitkuData['reference'] ?? null,
                'payment_details' => json_encode($duitkuData)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment URL created',
                'data' => [
                    'payment_url' => $duitkuData['paymentUrl'],
                    'reference' => $duitkuData['reference'],
                    'va_number' => $duitkuData['vaNumber'] ?? null,
                    'qr_string' => $duitkuData['qrString'] ?? null,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Duitku Create Payment Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle callback from Duitku
     */
    public function callback(Request $request)
    {
        Log::info('Duitku Callback Received', $request->all());

        $merchantCode = $request->input('merchantCode');
        $amount = $request->input('amount');
        $merchantOrderId = $request->input('merchantOrderId');
        $signature = $request->input('signature');
        $resultCode = $request->input('resultCode');

        // Verify signature
        if (!$this->duitkuService->verifyCallback($merchantCode, $amount, $merchantOrderId, $signature)) {
            Log::error('Duitku Callback: Invalid signature');
            return response()->json(['success' => false, 'message' => 'Invalid signature'], 400);
        }

        try {
            DB::beginTransaction();

            // Find order
            $order = Order::where('order_number', $merchantOrderId)->first();
            if (!$order) {
                Log::error('Duitku Callback: Order not found', ['order_number' => $merchantOrderId]);
                return response()->json(['success' => false, 'message' => 'Order not found'], 404);
            }

            // Find payment
            $payment = Payment::where('order_id', $order->id)
                ->where('method', 'DUITKU')
                ->latest()
                ->first();

            if (!$payment) {
                Log::error('Duitku Callback: Payment not found', ['order_id' => $order->id]);
                return response()->json(['success' => false, 'message' => 'Payment not found'], 404);
            }

            // Update payment status based on result code
            if ($resultCode === '00') {
                // Success - Use OrderService for consistent workflow (logs, events)
                $this->orderService->markAsPaid($order, [
                    'amount' => $amount,
                    'method' => 'DUITKU',
                    'external_id' => $request->input('reference'),
                ]);

                // Update the payment record created by createPayment with any final callback details
                if ($payment) {
                    $payment->update([
                        'transaction_status' => 'SUCCESS', // or 'settlement' to match Service
                        'payment_details' => json_encode($request->all())
                    ]);
                }

                Log::info('Duitku Callback: Payment success processed via OrderService', ['order_number' => $merchantOrderId]);
            } else {
                // Failed
                $payment->update([
                    'transaction_status' => 'FAILED',
                    'payment_details' => json_encode($request->all())
                ]);

                Log::info('Duitku Callback: Payment failed', [
                    'order_number' => $merchantOrderId,
                    'result_code' => $resultCode
                ]);
            }

            DB::commit();

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Duitku Callback Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Check payment status
     */
    public function checkStatus(Request $request)
    {
        $request->validate([
            'order_number' => 'required'
        ]);

        try {
            $order = Order::where('order_number', $request->order_number)->first();
            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            $result = $this->duitkuService->checkTransactionStatus($order->order_number);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to check status',
                    'error' => $result['message']
                ], 500);
            }

            return response()->json([
                'success' => true,
                'data' => $result['data']
            ]);

        } catch (\Exception $e) {
            Log::error('Duitku Check Status Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
