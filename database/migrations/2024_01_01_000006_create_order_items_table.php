<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sub_order_id')->constrained('sub_orders')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('product_name')->comment('Snapshot of product name at time of order');
            $table->decimal('unit_price', 10, 2)->comment('Price per unit at time of order');
            $table->integer('quantity');
            $table->decimal('total_price', 10, 2)->comment('unit_price * quantity');
            $table->timestamps();

            $table->index(['sub_order_id']);
            $table->index(['product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};

