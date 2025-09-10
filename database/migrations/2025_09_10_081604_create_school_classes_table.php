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
        Schema::create('school_classes', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // ex: CI, CP, 2nde...
            $table->string('level'); // primaire ou secondaire
            $table->string('sector'); // Maternelle/Primaire ou Secondaire
            $table->string('series')->nullable(); // A, B, C, D si Tle/1ère/2nde
            $table->foreignId('year_id')->constrained('years')->onDelete('cascade');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_classes');
    }
};
