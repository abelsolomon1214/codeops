<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->change();
            $table->string('phone', 20)->default('0911000000')->after('area');
            $table->index(['status', 'created_at']);
        });

        Schema::create('order_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('dish_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('qty');
            $table->unsignedInteger('unit_price_santim');
            $table->unsignedInteger('line_total_santim');
            $table->timestamps();

            $table->unique(['order_id', 'dish_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_lines');

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['status', 'created_at']);
            $table->dropColumn('phone');
        });
    }
};
