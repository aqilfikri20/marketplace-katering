<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void { Schema::create('orders', function (Blueprint $table) { $table->id(); $table->string('invoice_number')->unique(); $table->foreignId('customer_id')->constrained('users')->restrictOnDelete(); $table->foreignId('merchant_id')->constrained('users')->restrictOnDelete(); $table->date('delivery_date'); $table->text('delivery_address'); $table->text('notes')->nullable(); $table->decimal('subtotal', 12, 2); $table->decimal('delivery_fee', 12, 2)->default(0); $table->decimal('total', 12, 2); $table->string('status')->default('pending'); $table->timestamps(); }); }
    public function down(): void { Schema::dropIfExists('orders'); }
};
