<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->string('status', 20)->default('available')->after('is_available');
        });

        \Illuminate\Support\Facades\DB::table('menus')->where('is_available', false)->update(['status' => 'sold_out']);
    }

    public function down(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
