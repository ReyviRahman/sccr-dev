<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auth_users', function (Blueprint $table) {
            $table->id();

            $table->foreignId('identity_id')
                ->constrained('auth_identities')
                ->cascadeOnDelete();

            // Dipakai untuk login (nip / nidn / nim)
            $table->string('username', 50)->unique();

            $table->string('email')->nullable()->unique();

            $table->string('password');

            $table->timestamp('last_login_at')->nullable();

            $table->boolean('is_locked')->default(false);

            $table->rememberToken();

            $table->timestamps();

            $table->index('identity_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auth_users');
    }
};
