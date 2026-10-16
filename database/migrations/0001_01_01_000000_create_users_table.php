<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void { Schema::create('users', function (Blueprint $table) { $table->id(); $table->string('name'); $table->string('email')->unique(); $table->timestamp('email_verified_at')->nullable(); $table->string('password'); $table->string('role')->default('customer'); $table->string('company_name')->nullable(); $table->text('address')->nullable(); $table->string('phone', 30)->nullable(); $table->text('description')->nullable(); $table->string('food_type')->nullable(); $table->decimal('latitude', 10, 7)->nullable(); $table->decimal('longitude', 10, 7)->nullable(); $table->rememberToken(); $table->timestamps(); }); }
    public function down(): void { Schema::dropIfExists('users'); }
};
