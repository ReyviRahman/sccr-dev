<?php

// database/migrations/2025_10_08_000011_create_quiz_questions_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained('quizzes')->onDelete('cascade');
            $table->enum('type', ['multiple_choice', 'essay'])->default('multiple_choice');
            $table->text('question');
            $table->json('options')->nullable(); // Untuk pilihan ganda
            $table->integer('correct_index')->nullable(); // Index jawaban benar
            $table->text('answer_key')->nullable(); // Untuk essay
            $table->boolean('is_randomizable')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_questions');
    }
};
