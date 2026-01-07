<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use Illuminate\Http\Request;

class DisplayWebController extends Controller
{
    public function show(Request $request, $restaurantId)
    {
        $restaurant = Restaurant::findOrFail($restaurantId);
        return view('display.queue', compact('restaurant'));
    }
}
