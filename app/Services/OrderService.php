<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\OrderStatusLog;
use Illuminate\Support\Facades\DB;

class OrderService
{
    /**
     * Mark order as PAID and advance to QUEUED status.
     * This triggers the kitchen workflow.
     */
    public function markAsPaid(Order $order, $paymentData, $userId = null)
    {
        // Prevent double payment processing
        if ($order->payment_status === 'PAID') {
            return $order;
        }

        return DB::transaction(function () use ($order, $paymentData, $userId) {
            // 1. Create Payment Record
            Payment::create([
                'order_id' => $order->id,
                'amount' => $paymentData['amount'],
                'method' => $paymentData['method'], // CASH, QRIS
                'transaction_status' => 'settlement',
                'external_id' => $paymentData['external_id'] ?? null,
                'payment_details' => $paymentData['payment_details'] ?? null,
                'paid_at' => now(),
            ]);

            // 2. Update Order Status
            // From PENDING -> PAID (Financial) -> QUEUED (Kitchen)
            $oldStatus = $order->status;

            $order->update([
                'payment_status' => 'PAID',
                'status' => 'QUEUED' // Auto-queue to kitchen
            ]);

            // 3. Log Status Changes
            // Log PENDING -> PAID
            OrderStatusLog::create([
                'order_id' => $order->id,
                'from_status' => $oldStatus,
                'to_status' => 'PAID',
                'user_id' => $userId,
            ]);

            // Log PAID -> QUEUED
            OrderStatusLog::create([
                'order_id' => $order->id,
                'from_status' => 'PAID',
                'to_status' => 'QUEUED',
                'user_id' => $userId,
            ]);

            // Event: Notify Kitchen
            event(new \App\Events\OrderStatusChanged($order));

            return $order;
        });
    }
}
