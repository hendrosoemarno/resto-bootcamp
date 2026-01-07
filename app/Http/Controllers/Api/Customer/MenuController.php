<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Restaurant;
use App\Models\Table;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function index(Request $request, $restaurantId)
    {
        // 1. Validate Restaurant
        $restaurant = Restaurant::find($restaurantId);
        if (!$restaurant) {
            return response()->json(['message' => 'Restaurant not found'], 404);
        }

        // 2. Optional: Validate Table if provided (to ensure QR code is valid)
        if ($request->has('table_number')) {
            $table = Table::where('restaurant_id', $restaurantId)
                ->where('table_number', $request->table_number)
                ->first();

            if (!$table) {
                return response()->json(['message' => 'Table not found'], 404);
            }
        }

        // 3. Get Menus
        $menus = Menu::where('restaurant_id', $restaurantId)
            ->where('is_available', true)
            ->orderBy('category') // Group by category implicitly
            ->orderBy('name')
            ->get();

        return response()->json([
            'restaurant' => [
                'name' => $restaurant->name,
                'address' => $restaurant->address,
            ],
            'table_number' => $request->table_number ?? null,
            'menus' => $menus
        ]);
    }
}
