<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Menu;
use App\Models\Table;
use App\Models\OrderStatusLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validation
        $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id',
            'table_number' => 'required|string', // Send table_number, we'll find ID
            'customer_name' => 'nullable|string|max:50',
            'items' => 'required|array|min:1',
            'items.*.menu_id' => 'required|exists:menus,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.note' => 'nullable|string|max:100',
        ]);

        try {
            DB::beginTransaction();

            // 2. Resolve Table ID
            $table = Table::where('restaurant_id', $request->restaurant_id)
                ->where('table_number', $request->table_number)
                ->first();

            if (!$table) {
                return response()->json(['message' => 'Invalid Table Number'], 400);
            }

            // 3. Create Order Header
            $orderNumber = $this->generateOrderNumber();

            $order = Order::create([
                'restaurant_id' => $request->restaurant_id,
                'table_id' => $table->id,
                'order_number' => $orderNumber,
                'customer_name' => $request->customer_name ?? 'Guest',
                'total_amount' => 0, // Calculated below
                'payment_status' => 'UNPAID',
                'status' => 'PENDING',
            ]);

            // 4. Process Items & Calculate Total
            $totalAmount = 0;

            // Pre-fetch menus to avoid N+1 and ensure price integrity
            $menuIds = collect($request->items)->pluck('menu_id');
            $menus = Menu::whereIn('id', $menuIds)->get()->keyBy('id');

            foreach ($request->items as $item) {
                $menu = $menus[$item['menu_id']] ?? null;

                if (!$menu || !$menu->is_available) {
                    throw new \Exception("Menu ID {$item['menu_id']} is unavailable.");
                }

                $price = $menu->price;
                $subtotal = $price * $item['quantity'];
                $totalAmount += $subtotal;

                OrderItem::create([
                    'order_id' => $order->id,
                    'menu_id' => $menu->id,
                    'quantity' => $item['quantity'],
                    'price' => $price,
                    'note' => $item['note'] ?? null,
                ]);
            }

            // 5. Update Order Total
            $order->update(['total_amount' => $totalAmount]);

            // 6. Log Status
            OrderStatusLog::create([
                'order_id' => $order->id,
                'from_status' => 'INIT',
                'to_status' => 'PENDING',
                'user_id' => null, // Customer
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Order created successfully',
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'total_amount' => $totalAmount,
                'status' => 'PENDING',
                'payment_action' => [
                    'url' => url("/payment/{$order->order_number}"), // Placeholder for payment page
                    'valid_until' => now()->addMinutes(30),
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Order failed: ' . $e->getMessage()], 500);
        }
    }

    public function show($orderNumber)
    {
        $order = Order::with(['items.menu', 'table'])
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        return response()->json($order);
    }

    private function generateOrderNumber()
    {
        // Format: ORD-YYYYMMDD-XXXX (Random String)
        // Example: ORD-20251227-AB12
        return 'ORD-' . date('Ymd') . '-' . strtoupper(Str::random(4));
    }
}
