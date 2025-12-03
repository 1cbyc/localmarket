<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique()->comment('Human-readable order identifier');
            $table->foreignId('customer_id')->constrained('users')->onDelete('cascade');
            $table->decimal('total_amount', 10, 2)->comment('Total order amount including all sub-orders');
            $table->decimal('subtotal', 10, 2)->comment('Subtotal before fees');
            $table->decimal('total_delivery_fee', 10, 2)->default(0)->comment('Sum of all delivery fees');
            $table->decimal('total_commission', 10, 2)->default(0)->comment('Total platform commission');
            $table->enum('payment_status', ['pending', 'processing', 'completed', 'failed', 'refunded'])->default('pending');
            $table->enum('order_status', ['pending', 'confirmed', 'processing', 'completed', 'cancelled'])->default('pending');
            $table->string('payment_intent_id')->nullable()->comment('Stripe payment intent ID');
            $table->text('customer_address')->nullable();
            $table->decimal('customer_lat', 10, 8)->nullable();
            $table->decimal('customer_lng', 11, 8)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['customer_id']);
            $table->index(['order_number']);
            $table->index(['payment_status']);
            $table->index(['order_status']);
            $table->index(['created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};

