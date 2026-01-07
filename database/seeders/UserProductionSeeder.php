<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Restaurant;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserProductionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pastikan ada minimal 1 restoran
        $restId = DB::table('restaurants')->value('id');

        if (!$restId) {
            $restId = DB::table('restaurants')->insertGetId([
                'name' => 'Restoran Utama',
                'address' => 'Alamat Default',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->command->info("Restoran default dibuat (ID: $restId).");
        }

        // Daftar User Baru yang ingin dibuat/direset
        $users = [
            [
                'email' => 'owner@resto.com',
                'name' => 'Owner Resto',
                'role' => 'admin',
                'password' => 'RahasiaOwner123'
            ],
            [
                'email' => 'dapur@resto.com',
                'name' => 'Kepala Dapur',
                'role' => 'chef',
                'password' => 'MasakEnak2024'
            ],
            [
                'email' => 'kasir@resto.com',
                'name' => 'Kasir Utama',
                'role' => 'cashier',
                'password' => 'CuanLancar2024'
            ],
        ];

        foreach ($users as $u) {
            User::updateOrCreate(
                ['email' => $u['email']], // Cari berdasarkan email
                [
                    'name' => $u['name'],
                    'role' => $u['role'],
                    'restaurant_id' => $restId,
                    'password' => Hash::make($u['password']) // Hash password baru
                ]
            );
            $this->command->info("User {$u['role']} ({$u['email']}) berhasil dibuat/diupdate.");
        }
    }
}
