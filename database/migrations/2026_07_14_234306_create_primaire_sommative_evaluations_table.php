<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void  {
        Schema::create('primaire_sommative_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('composition_id')->constrained('primaire_compositions')->onDelete('cascade');
            $table->foreignId('classe_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade');
            // Barème choisi par l'enseignant à la première saisie : note_min = 5 ou 10, note_max = double
            $table->decimal('note_min', 5, 2)->default(10);
            $table->decimal('note_max', 5, 2)->default(20);
            $table->timestamps();

            // Une seule évaluation par composition + classe + matière
            $table->unique(['composition_id', 'classe_id', 'subject_id'], 'sommative_eval_unique');
        });
    }

    public function down(): void  {
        Schema::dropIfExists('primaire_sommative_evaluations');
    }
};