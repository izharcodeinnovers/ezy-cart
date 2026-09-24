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
            $table->string('order_number')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('address_id')->constrained()->onDelete('cascade');
            $table->foreignId('coupon_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('subtotal', 12, 2);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('shipping_amount', 12, 2)->default(0);
            $table->decimal('grand_total', 12, 2);
            $table->enum('payment_status', ['pending', 'processing', 'completed', 'failed', 'refunded', 'partially_refunded'])->default('pending');
            $table->enum('order_status', ['pending', 'confirmed', 'processing', 'packed', 'shipped', 'out_for_delivery', 'delivered', 'cancelled', 'refunded'])->default('pending');
            $table->enum('shipping_method', ['standard', 'express', 'overnight', 'pickup'])->default('standard');
            $table->enum('payment_method', ['credit_card', 'debit_card', 'paypal', 'stripe', 'bank_transfer', 'cash_on_delivery'])->default('cash_on_delivery');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};