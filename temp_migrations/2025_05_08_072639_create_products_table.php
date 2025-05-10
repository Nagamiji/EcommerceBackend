<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->integer('stock_quantity');
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('image_url')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamps(0);  // This will create 'created_at' and 'updated_at'
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
