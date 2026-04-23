<?php

// database/migrations/2025_10_08_000005_create_attendances_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lms_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('participant_id')->constrained('participants')->onDelete('cascade');
            $table->integer('duration')->default(0); // Lama hadir (menit)
            $table->string('ip_address')->nullable();
            $table->string('device')->nullable(); // Info browser/device
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lms_attendances');
    }
};
