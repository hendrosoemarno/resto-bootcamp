<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use Illuminate\Http\Request;

class AdminMenuController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'price' => 'required|numeric',
            'category' => 'required|string',
            'image_url' => 'nullable|url',
        ]);

        $user = $request->user();

        $menu = Menu::create([
            'restaurant_id' => $user->restaurant_id,
            'name' => $request->name,
            'price' => $request->price,
            'category' => $request->category,
            'image_url' => $request->image_url,
            'is_available' => true,
        ]);

        return response()->json(['message' => 'Menu created', 'menu' => $menu]);
    }

    public function update(Request $request, $id)
    {
        $menu = Menu::where('id', $id)->where('restaurant_id', $request->user()->restaurant_id)->firstOrFail();

        $request->validate([
            'name' => 'sometimes|string',
            'price' => 'sometimes|numeric',
            'is_available' => 'boolean'
        ]);

        $menu->update($request->all());

        return response()->json(['message' => 'Menu updated', 'menu' => $menu]);
    }

    public function destroy(Request $request, $id)
    {
        $menu = Menu::where('id', $id)->where('restaurant_id', $request->user()->restaurant_id)->firstOrFail();
        $menu->delete();
        return response()->json(['message' => 'Menu deleted']);
    }
}
