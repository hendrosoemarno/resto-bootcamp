<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Models\Order;
use Illuminate\Http\Request;

class CustomerWebController extends Controller
{
    public function index(Request $request, $restaurantId)
    {
        $tableNumber = $request->query('table');

        // Basic validation
        $restaurant = Restaurant::findOrFail($restaurantId);

        if (!$tableNumber) {
            abort(404, 'Table number is required. Please scan the QR code via table.');
        }

        return view('customer.menu', compact('restaurant', 'tableNumber'));
    }

    public function status($orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)->firstOrFail();
        return view('customer.status', compact('order'));
    }
}
