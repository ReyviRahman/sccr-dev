<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lms_video_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('lms_rooms')->onDelete('cascade');
            $table->string('session_id')->unique();
            $table->string('host_nip');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lms_video_sessions');
    }
};
