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
}
