<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CashierWebController extends Controller
{
    public function login()
    {
        return view('cashier.login');
    }

    public function dashboard()
    {
        return view('cashier.dashboard');
    }

    public function printReceipt($id)
    {
        $order = \App\Models\Order::with(['items.menu', 'table', 'restaurant', 'payment'])->findOrFail($id);

        return view('cashier.receipt', compact('order'));
    }
}
