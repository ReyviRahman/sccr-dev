<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('code', 5)->unique();       // e.g. IDR, USD
            $table->string('name');                   // e.g. Rupiah, US Dollar
            $table->string('symbol', 5);              // e.g. Rp, $
            $table->boolean('is_base')->default(false); // Mata uang dasar
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
