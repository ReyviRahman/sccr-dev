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
        Schema::create('auth_role_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('auth_roles')->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained('auth_permissions')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auth_role_permissions');
    }
};
