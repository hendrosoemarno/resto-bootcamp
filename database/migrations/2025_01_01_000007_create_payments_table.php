<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 12, 2);
            $table->string('method'); // CASH, QRIS, BANK_TRANSFER
            $table->string('transaction_status')->nullable(); // settlement, pending, expire (from gateway)
            $table->string('external_id')->nullable(); // Order ID from Payment Gateway
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
