<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auth_identities', function (Blueprint $table) {
            $table->id();

            // Domain identity
            $table->enum('identity_type', ['employee', 'lecturer', 'student']);

            // NIP / NIDN / NIM (boleh mengandung spasi)
            $table->string('identity_key', 30);

            // Optional: relasi ke unit / holding / campus
            $table->unsignedBigInteger('holding_id')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // Kombinasi unik
            $table->unique(['identity_type', 'identity_key'], 'auth_identity_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auth_identities');
    }
};
