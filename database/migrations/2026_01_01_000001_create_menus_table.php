<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void { Schema::create('menus', function (Blueprint $table) { $table->id(); $table->foreignId('merchant_id')->constrained('users')->cascadeOnDelete(); $table->string('name'); $table->text('description'); $table->decimal('price', 12, 2); $table->string('photo')->nullable(); $table->string('category')->nullable(); $table->boolean('is_available')->default(true); $table->timestamps(); }); }
    public function down(): void { Schema::dropIfExists('menus'); }
};
