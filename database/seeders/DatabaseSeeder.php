<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Restaurant
        $restaurantId = DB::table('restaurants')->insertGetId([
            'name' => 'Restoran Nusantara',
            'address' => 'Jl. Merdeka No. 1, Jakarta',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Create Users
        DB::table('users')->insert([
            [
                'name' => 'Admin Owner',
                'email' => 'admin@resto.com',
                'password' => Hash::make('password'),
                'restaurant_id' => $restaurantId,
                'role' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Chef Juna',
                'email' => 'chef@resto.com',
                'password' => Hash::make('password'),
                'restaurant_id' => $restaurantId,
                'role' => 'chef',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Kasir Siti',
                'email' => 'kasir@resto.com',
                'password' => Hash::make('password'),
                'restaurant_id' => $restaurantId,
                'role' => 'cashier',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        // 3. Create Tables
        $tables = [];
        for ($i = 1; $i <= 10; $i++) {
            $tables[] = [
                'restaurant_id' => $restaurantId,
                'table_number' => 'T' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'qr_code_string' => 'https://resto.com/order?table=' . $i,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::table('tables')->insert($tables);

        // 4. Create Menus
        $menus = [
            ['name' => 'Nasi Goreng Spesial', 'price' => 35000, 'category' => 'food'],
            ['name' => 'Ayam Bakar Madu', 'price' => 28000, 'category' => 'food'],
            ['name' => 'Es Teh Manis', 'price' => 5000, 'category' => 'drink'],
            ['name' => 'Kopi Tubruk', 'price' => 12000, 'category' => 'drink'],
            ['name' => 'Kentang Goreng', 'price' => 15000, 'category' => 'snack'],
        ];

        foreach ($menus as $menu) {
            DB::table('menus')->insert([
                'restaurant_id' => $restaurantId,
                'name' => $menu['name'],
                'price' => $menu['price'],
                'category' => $menu['category'],
                'is_available' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
