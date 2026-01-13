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
     * Show payment method selection page
     */
    public function selectPayment($orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)->firstOrFail();

        if ($order->payment_status === 'PAID') {
            return redirect()->route('customer.status', $orderNumber);
        }

        $methods = $this->duitkuService->getPaymentMethods($order->total_amount);

        return view('customer.select-payment', compact('order', 'methods'));
    }

    /**
     * Create payment request
     */
    public function createPayment(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'payment_method' => 'required|string'
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

            // Update order with selected payment method temporarily (optional)

            // Create transaction with Duitku
            $result = $this->duitkuService->createTransaction($order, $request->payment_method);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                    'error' => $result['error'] ?? null
                ], 500);
            }

            $duitkuData = $result['data'];

            // Save or update payment record
            $payment = Payment::updateOrCreate(
                ['order_id' => $order->id],
                [
                    'amount' => $order->total_amount,
                    'method' => $request->payment_method,
                    'transaction_status' => 'PENDING',
                    'transaction_id' => $duitkuData['reference'] ?? null,
                    'payment_details' => json_encode($duitkuData)
                ]
            );

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
            Log::error('Duitku Callback: Invalid signature', [
                'received' => $signature,
                'merchantCode' => $merchantCode,
                'amount' => $amount,
                'merchantOrderId' => $merchantOrderId,
                'expected' => md5($merchantCode . (int) $amount . $merchantOrderId . config('duitku.api_key'))
            ]);
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

            // Find existing payment record (created in createPayment)
            $payment = Payment::where('order_id', $order->id)
                ->where('transaction_status', 'PENDING')
                ->latest()
                ->first();

            // Update payment status based on result code
            if ($resultCode === '00') {
                // Success - Use OrderService for consistent workflow (logs, events)
                $this->orderService->markAsPaid($order, [
                    'amount' => $amount,
                    'method' => 'DUITKU-' . ($request->input('paymentCode') ?? 'ONLINE'),
                    'external_id' => $request->input('reference'),
                ]);

                // Update the payment record details (Note: markAsPaid creates its own record, 
                // but we keep this for audit trail if it exists)
                if ($payment) {
                    $payment->update([
                        'transaction_status' => 'SUCCESS',
                        'payment_details' => json_encode($request->all())
                    ]);
                }

                Log::info('Duitku Callback: Payment success processed', ['order_number' => $merchantOrderId]);
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
     * Check payment status and sync with Duitku
     */
    public function checkStatus(Request $request)
    {
        $request->validate([
            'order_number' => 'required'
        ]);

        try {
            $order = Order::where('order_number', $request->order_number)->first();
            if (!$order) {
                return response()->json(['success' => false, 'message' => 'Order not found'], 404);
            }

            // If already paid locally, no need to check remote
            if ($order->payment_status === 'PAID') {
                return response()->json([
                    'success' => true,
                    'message' => 'Order already paid locally',
                    'payment_status' => 'PAID'
                ]);
            }

            $result = $this->duitkuService->checkTransactionStatus($order->order_number);

            if (!$result['success']) {
                $errorMessage = $result['message'];
                // Handle specific Duitku transition error like "transaction not found"
                if (isset($result['error']['statusMessage'])) {
                    $errorMessage = $result['error']['statusMessage'];
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Gagal cek Duitku: ' . $errorMessage . '. (Pastikan pelanggan sudah klik "Bayar" di HP/Halaman Status)',
                    'error' => $result['error'] ?? null
                ], 400);
            }

            $data = $result['data'];
            // Duitku statusCode '00' means success/paid
            $statusCode = $data['statusCode'] ?? ($data['resultCode'] ?? null);

            if ($statusCode === '00') {
                // Sync status to local DB
                $this->orderService->markAsPaid($order, [
                    'amount' => $data['amount'] ?? $order->total_amount,
                    'method' => 'DUITKU-SYNC',
                    'external_id' => $data['reference'] ?? null,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Payment status synced: PAID',
                    'payment_status' => 'PAID',
                    'data' => $data
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment status: ' . ($data['statusMessage'] ?? 'PENDING'),
                'payment_status' => $order->payment_status,
                'data' => $data
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
