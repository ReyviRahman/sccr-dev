<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auth_role_modules', function (Blueprint $table) {
            $table->id();

            $table->foreignId('role_id')
                ->constrained('auth_roles')
                ->cascadeOnDelete();

            /**
             * module_code = auth_modules.code
             * sengaja pakai code, bukan id
             * supaya:
             * - portable
             * - readable
             * - stabil saat sync environment
             */
            $table->string('module_code', 10);

            /**
             * view  = hanya dashboard / laporan
             * full  = CRUD + action
             */
            $table->enum('access_level', ['view', 'full'])
                ->default('view');

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->unique(['role_id', 'module_code']);
            $table->index(['module_code', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auth_role_modules');
    }
};
