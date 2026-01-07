<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class AdminCategoryController extends Controller
{
    public function index(Request $request)
    {
        $restaurantId = $request->user()->restaurant_id;

        // --- AUTO-SYNC LOGIC ---
        // 1. Ambil kategori unik yang benar-benar dipakai di menu
        $activeCategories = \App\Models\Menu::where('restaurant_id', $restaurantId)
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->pluck('category');

        // 2. Masukkan ke tabel categories jika belum ada
        foreach ($activeCategories as $catName) {
            $cleanName = trim($catName);
            if (!$cleanName)
                continue;

            // Cek existensi manual biar aman
            $exists = Category::where('restaurant_id', $restaurantId)
                ->where('name', $cleanName)
                ->exists();

            if (!$exists) {
                Category::create([
                    'restaurant_id' => $restaurantId,
                    'name' => $cleanName
                ]);
            }
        }
        // -----------------------

        $categories = Category::where('restaurant_id', $restaurantId)
            ->orderBy('name')
            ->get();

        return response()->json($categories);
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:50']);

        $category = Category::create([
            'restaurant_id' => $request->user()->restaurant_id,
            'name' => trim($request->name)
        ]);

        return response()->json(['message' => 'Kategori berhasil dibuat', 'category' => $category]);
    }

    public function update(Request $request, $id)
    {
        $request->validate(['name' => 'required|string|max:50']);

        $category = Category::where('id', $id)
            ->where('restaurant_id', $request->user()->restaurant_id)
            ->firstOrFail();

        $category->update(['name' => trim($request->name)]);

        return response()->json(['message' => 'Kategori berhasil diupdate', 'category' => $category]);
    }

    public function destroy(Request $request, $id)
    {
        $category = Category::where('id', $id)
            ->where('restaurant_id', $request->user()->restaurant_id)
            ->firstOrFail();

        $category->delete();

        return response()->json(['message' => 'Kategori berhasil dihapus']);
    }
}
