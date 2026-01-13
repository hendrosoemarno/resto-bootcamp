<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\CustomerWebController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Customer Pages (QR Scan)
Route::get('/order/{restaurantId}', [CustomerWebController::class, 'index'])->name('customer.menu');
Route::get('/order/status/{orderNumber}', [CustomerWebController::class, 'status'])->name('customer.status');
Route::get('/order/status/{orderNumber}/payment', [\App\Http\Controllers\Api\Payment\DuitkuController::class, 'selectPayment'])->name('payment.select');

// Kitchen Pages (Staff)
Route::get('/kitchen/login', [\App\Http\Controllers\Web\KitchenWebController::class, 'login'])->name('kitchen.login');
Route::get('/kitchen/dashboard', [\App\Http\Controllers\Web\KitchenWebController::class, 'dashboard'])->name('kitchen.dashboard');

// Cashier Pages (Staff)
Route::get('/cashier/login', [\App\Http\Controllers\Web\CashierWebController::class, 'login'])->name('cashier.login');
Route::get('/cashier/dashboard', [\App\Http\Controllers\Web\CashierWebController::class, 'dashboard'])->name('cashier.dashboard');

// Display Screen (Public TV)
Route::get('/display/{restaurantId}', [\App\Http\Controllers\Web\DisplayWebController::class, 'show'])->name('display.queue');

// Admin Pages (Owner)
Route::get('/admin/login', [\App\Http\Controllers\Web\AdminWebController::class, 'login'])->name('admin.login');
Route::get('/admin/dashboard', [\App\Http\Controllers\Web\AdminWebController::class, 'dashboard'])->name('admin.dashboard');

Route::get('/', function () {
    return view('welcome');
});
