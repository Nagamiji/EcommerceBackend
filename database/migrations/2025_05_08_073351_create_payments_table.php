<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->enum('payment_status', ['pending', 'completed', 'failed'])->default('pending');
            $table->decimal('amount', 10, 2);
            $table->enum('payment_method', ['credit_card', 'paypal', 'bank_transfer']);
            $table->timestamps(0); // For precise created_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
