<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dishes', function (Blueprint $table) {
            $table->foreignId('restaurant_id')
                ->nullable()
                ->after('name')
                ->constrained()
                ->cascadeOnDelete();

            $table->dropColumn('restaurant');
        });
    }

    public function down(): void
    {
        Schema::table('dishes', function (Blueprint $table) {
            $table->string('restaurant')->default('');
            $table->dropConstrainedForeignId('restaurant_id');
        });
    }
};