<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class KitchenWebController extends Controller
{
    public function login()
    {
        return view('kitchen.login');
    }

    public function dashboard()
    {
        // View only. Data loaded via API
        return view('kitchen.dashboard');
    }
}
