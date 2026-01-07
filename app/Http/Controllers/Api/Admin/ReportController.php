<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $restaurantId = $request->user()->restaurant_id;
        $startDate = $request->start_date ?? Carbon::now()->startOfMonth();
        $endDate = $request->end_date ?? Carbon::now()->endOfMonth();

        // 1. Summary Stats
        $todayRevenue = Order::where('restaurant_id', $restaurantId)
            ->where('payment_status', 'PAID')
            ->whereDate('created_at', Carbon::today())
            ->sum('total_amount');

        $monthRevenue = Order::where('restaurant_id', $restaurantId)
            ->where('payment_status', 'PAID')
            ->whereMonth('created_at', Carbon::now()->month)
            ->sum('total_amount');

        // 2. Daily Sales Trend (for Chart)
        $dailySales = Order::where('restaurant_id', $restaurantId)
            ->where('payment_status', 'PAID')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total_amount) as total'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        // 3. Transactions List (Recent)
        $transactions = Order::where('restaurant_id', $restaurantId)
            ->where('payment_status', 'PAID')
            ->with(['items'])
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        // 4. Product Sales (Best Sellers)
        $productSales = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('menus', 'order_items.menu_id', '=', 'menus.id')
            ->where('orders.restaurant_id', $restaurantId)
            ->where('orders.payment_status', 'PAID')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->select(
                'menus.name as menu_name',
                'menus.category',
                DB::raw('SUM(order_items.quantity) as total_qty'),
                DB::raw('SUM(order_items.price * order_items.quantity) as total_revenue')
            )
            ->groupBy('menus.id', 'menus.name', 'menus.category')
            ->orderByDesc('total_qty')
            ->limit(20)
            ->get();

        return response()->json([
            'summary' => [
                'today' => $todayRevenue,
                'this_month' => $monthRevenue,
                'total_transactions' => $transactions->count()
            ],
            'daily_trend' => $dailySales,
            'transactions' => $transactions,
            'product_sales' => $productSales
        ]);
    }
}
