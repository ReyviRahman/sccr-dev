<?php

// database/migrations/2025_10_08_000014_create_quiz_results_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained('quizzes')->onDelete('cascade');
            $table->string('student_nim', 20);
            $table->foreign('student_nim')->references('nim')->on('students')->onDelete('cascade');
            $table->float('total_score')->default(0);
            $table->integer('duration')->nullable(); // Lama pengerjaan (menit)
            $table->timestamps();

            $table->unique(['quiz_id', 'student_nim']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_results');
    }
};
