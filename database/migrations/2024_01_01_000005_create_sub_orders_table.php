<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sub_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('shop_id')->constrained('shops')->onDelete('cascade');
            $table->foreignId('rider_id')->nullable()->constrained('users')->onDelete('set null');
            $table->decimal('subtotal', 10, 2)->comment('Subtotal for this vendor');
            $table->decimal('delivery_fee', 10, 2)->default(0)->comment('Delivery fee for this sub-order');
            $table->decimal('commission_amount', 10, 2)->default(0)->comment('Platform commission');
            $table->decimal('vendor_amount', 10, 2)->comment('Amount to be paid to vendor');
            $table->enum('delivery_status', ['pending', 'finding_driver', 'picked_up', 'delivered', 'cancelled'])->default('pending');
            $table->timestamp('picked_up_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->text('delivery_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['order_id']);
            $table->index(['shop_id']);
            $table->index(['rider_id']);
            $table->index(['delivery_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sub_orders');
    }
};

