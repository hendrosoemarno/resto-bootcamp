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
            'image' => 'nullable|image|max:2048', // 2MB max
            'image_url' => 'nullable|string'
        ]);

        $user = $request->user();
        $imageUrl = $request->image_url;

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('menus', 'public');
            // FIX: Gunakan url() pembungkus Storage::url() agar support subfolder
            $imageUrl = url(\Illuminate\Support\Facades\Storage::url($path));
        }

        $menu = Menu::create([
            'restaurant_id' => $user->restaurant_id,
            'name' => $request->name,
            'price' => $request->price,
            'category' => $request->category,
            'image_url' => $imageUrl,
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
            'is_available' => 'boolean',
            'image' => 'nullable|image|max:2048'
        ]);

        $data = $request->except(['image', '_method']);

        if ($request->has('is_available')) {
            $data['is_available'] = filter_var($request->is_available, FILTER_VALIDATE_BOOLEAN);
        }

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('menus', 'public');
            // FIX: Gunakan url() pembungkus Storage::url() agar support subfolder
            $data['image_url'] = url(\Illuminate\Support\Facades\Storage::url($path));
        } elseif ($request->has('image_url') && $request->image_url) {
            $data['image_url'] = $request->image_url;
        }

        $menu->update($data);

        return response()->json(['message' => 'Menu updated', 'menu' => $menu]);
    }

    public function destroy(Request $request, $id)
    {
        $menu = Menu::where('id', $id)->where('restaurant_id', $request->user()->restaurant_id)->firstOrFail();
        $menu->delete();
        return response()->json(['message' => 'Menu deleted']);
    }
}
