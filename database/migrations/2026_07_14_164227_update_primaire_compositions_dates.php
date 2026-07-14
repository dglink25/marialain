<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('primaire_compositions', function (Blueprint $table) {
            // Remplacer les colonnes texte par des plages de dates
            $table->dropColumn(['periode_composition', 'periode_saisie']);
        });

        Schema::table('primaire_compositions', function (Blueprint $table) {
            $table->date('composition_debut')->nullable()->after('mois');
            $table->date('composition_fin')->nullable()->after('composition_debut');
            $table->date('saisie_debut')->nullable()->after('composition_fin');
            $table->date('saisie_fin')->nullable()->after('saisie_debut');
        });
    }

    public function down(): void
    {
        Schema::table('primaire_compositions', function (Blueprint $table) {
            $table->dropColumn(['composition_debut', 'composition_fin', 'saisie_debut', 'saisie_fin']);
        });

        Schema::table('primaire_compositions', function (Blueprint $table) {
            $table->string('periode_composition')->nullable();
            $table->string('periode_saisie')->nullable();
        });
    }
};
