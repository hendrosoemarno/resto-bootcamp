<?php

namespace App\Http\Controllers\Api\Kitchen;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatusLog;
use App\Events\OrderStatusChanged;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KitchenController extends Controller
{
    /**
     * Get active kitchen orders (QUEUED, COOKING, READY)
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $orders = Order::with(['items.menu', 'table'])
            ->where('restaurant_id', $user->restaurant_id)
            ->whereIn('status', ['QUEUED', 'COOKING', 'READY'])
            ->orderBy('updated_at', 'asc') // First in, First out
            ->get();

        return response()->json($orders);
    }

    /**
     * Chef starts cooking
     * QUEUED -> COOKING
     */
    public function startCooking(Request $request, $orderId)
    {
        return $this->updateStatus($request, $orderId, 'QUEUED', 'COOKING');
    }

    /**
     * Chef finishes cooking
     * COOKING -> READY
     */
    public function markReady(Request $request, $orderId)
    {
        return $this->updateStatus($request, $orderId, 'COOKING', 'READY');
    }

    /**
     * Waiter picks up / Customer takes order
     * READY -> COMPLETED
     */
    public function markCompleted(Request $request, $orderId)
    {
        return $this->updateStatus($request, $orderId, 'READY', 'COMPLETED');
    }

    private function updateStatus(Request $request, $orderId, $fromStatus, $toStatus)
    {
        $user = $request->user();

        // 1. Find Order
        $order = Order::where('restaurant_id', $user->restaurant_id)
            ->where('id', $orderId)
            ->firstOrFail();

        // 2. Validate Transitions
        if ($order->status !== $fromStatus && $order->status !== $toStatus) {
            return response()->json([
                'message' => "Invalid status transition. Current: {$order->status}, Expected: $fromStatus"
            ], 400);
        }

        // Idempotency: if already updated, just return
        if ($order->status === $toStatus) {
            return response()->json(['message' => 'Order already updated', 'order' => $order]);
        }

        DB::transaction(function () use ($order, $fromStatus, $toStatus, $user) {
            // 3. Update Status
            $order->update(['status' => $toStatus]);

            // 4. Log
            OrderStatusLog::create([
                'order_id' => $order->id,
                'from_status' => $fromStatus,
                'to_status' => $toStatus,
                'user_id' => $user->id,
            ]);

            // 5. Broadcast Event
            event(new OrderStatusChanged($order));
        });

        return response()->json(['message' => "Order $toStatus", 'order' => $order]);
    }
}
