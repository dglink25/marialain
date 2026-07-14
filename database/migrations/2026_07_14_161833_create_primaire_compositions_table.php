<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{
    public function up(): void  {
        Schema::create('primaire_compositions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->json('classe_ids');                    // IDs des classes concernées
            $table->unsignedTinyInteger('mois');           // 1-10 (mois scolaires)
            $table->string('periode_composition');         // ex: "Du 05/11 au 09/11/2025"
            $table->string('periode_saisie');              // ex: "Du 10/11 au 15/11/2025"
            $table->timestamps();
        });
    }

    public function down(): void  {
        Schema::dropIfExists('primaire_compositions');
    }
};
