<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->onDelete('cascade');
            $table->foreignId('table_id')->constrained()->onDelete('cascade');
            $table->string('order_number')->unique(); // ORD-YYYYMMDD-XXXX
            $table->string('customer_name')->nullable();
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->enum('payment_status', ['UNPAID', 'PAID'])->default('UNPAID');
            $table->enum('status', ['PENDING', 'PAID', 'QUEUED', 'COOKING', 'READY', 'COMPLETED'])->default('PENDING'); // State Machine
            $table->timestamps();

            $table->index(['restaurant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
