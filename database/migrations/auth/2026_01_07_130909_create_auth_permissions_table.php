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
        Schema::create('auth_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50); // INV_CREATE
            $table->string('module_code', 10); // 01005
            $table->string('description')->nullable();
            $table->timestamps();

            $table->unique(['code', 'module_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auth_permissions');
    }
};
