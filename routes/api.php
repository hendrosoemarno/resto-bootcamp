<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Customer\MenuController;
use App\Http\Controllers\Api\Customer\OrderController;
use App\Http\Controllers\Api\Payment\PaymentController;
use App\Http\Controllers\Api\Kitchen\KitchenController;

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Display\OrderDisplayController;

// Customer Routes (Public / No Login)
Route::prefix('v1')->group(function () {

    // Auth Staff
    Route::post('/login', [AuthController::class, 'login']);

    // Display Antrian (Public)
    Route::get('/restaurants/{restaurantId}/display/orders', [OrderDisplayController::class, 'index']);

    // Menu
    // Example: /api/v1/restaurants/1/menu?table_number=T01
    Route::get('/restaurants/{restaurantId}/menu', [MenuController::class, 'index']);

    // Order
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/{orderNumber}', [OrderController::class, 'show']);

    // Payment Callback (Simulated Webhook - Public for Gateway access)
    Route::post('/payments/callback', [PaymentController::class, 'callback']);

});

// Staff Routes (Protected)
Route::middleware('auth:sanctum')->prefix('v1')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Cashier: Manual Payment
    Route::post('/cashier/payments/confirm', [PaymentController::class, 'manualConfirm']);

    // Cashier: List Unpaid Orders (Quick Inline Route for now)
    Route::get('/cashier/orders', function (Request $request) {
        return \App\Models\Order::with(['items.menu', 'table'])
            ->where('restaurant_id', $request->user()->restaurant_id)
            ->where('payment_status', 'UNPAID')
            ->orderBy('created_at', 'desc')
            ->get();
    });

    // Kitchen: Chef Operations
    Route::get('/kitchen/orders', [KitchenController::class, 'index']);
    Route::put('/kitchen/orders/{id}/start', [KitchenController::class, 'startCooking']);
    Route::put('/kitchen/orders/{id}/ready', [KitchenController::class, 'markReady']);
    Route::put('/kitchen/orders/{id}/complete', [KitchenController::class, 'markCompleted']);

    // Admin: Menu Management
    Route::post('/admin/menus', [\App\Http\Controllers\Api\Admin\AdminMenuController::class, 'store']);
    Route::put('/admin/menus/{id}', [\App\Http\Controllers\Api\Admin\AdminMenuController::class, 'update']);
    Route::delete('/admin/menus/{id}', [\App\Http\Controllers\Api\Admin\AdminMenuController::class, 'destroy']);

    // Admin: Reports
    Route::get('/admin/reports', [\App\Http\Controllers\Api\Admin\ReportController::class, 'index']);

});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
