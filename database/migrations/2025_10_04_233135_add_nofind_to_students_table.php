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
       Schema::table('students', function (Blueprint $table) {
            // Colonne manquante pour la classe
            $table->unsignedBigInteger('class_id')->nullable()->after('academic_year_id');

            // Colonnes pour les fichiers
            $table->string('birth_certificate')->nullable()->after('class_id');
            $table->string('vaccination_card')->nullable()->after('birth_certificate');
            $table->string('previous_report_card')->nullable()->after('vaccination_card');
            $table->string('diploma_certificate')->nullable()->after('previous_report_card');

            // Colonnes pour les informations des parents
            $table->string('parent_full_name')->nullable()->after('diploma_certificate');
            $table->string('parent_email')->nullable()->after('parent_full_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
       Schema::table('students', function (Blueprint $table) {
            $table->dropColumn([
                'class_id',
                'birth_certificate',
                'vaccination_card',
                'previous_report_card',
                'diploma_certificate',
                'parent_full_name',
                'parent_email',
            ]);
        });
    }
};
