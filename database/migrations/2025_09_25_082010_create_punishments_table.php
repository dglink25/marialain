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
        Schema::create('punishments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade'); // Élève puni
            $table->foreignId('academic_year_id')->constrained()->onDelete('cascade'); // Année active
            $table->foreignId('entity_id')->constrained()->onDelete('cascade'); // secondaire, primaire
            $table->text('reason'); // motif de la punition
            $table->date('date_punishment');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('punishments');
    }
};
