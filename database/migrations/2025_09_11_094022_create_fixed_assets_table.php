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
        Schema::create('fixed_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('holding_id')->constrained('holdings')->onDelete('cascade');
            $table->string('name');
            $table->date('acquisition_date');
            $table->decimal('value', 20, 2);
            $table->decimal('depreciation_rate', 5, 2);
            $table->foreignId('account_id')->nullable()->constrained('accounts')->onDelete('set null'); // akun aset
            $table->enum('status', ['active', 'sold', 'disposed'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fixed_assets');
    }
};
