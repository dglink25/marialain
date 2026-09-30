<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Ajouter la clé d'idempotence si elle n'existe pas encore
        Schema::table('cahier_de_texte', function (Blueprint $table) {
            $table->uuid('idempotency_key')
                ->nullable()
                ->unique()
                ->after('id');
        });

        // 2. Supprimer les doublons avant de créer la contrainte UNIQUE.
        //
        // On conserve la ligne ayant le plus petit ID
        // et on supprime les autres lignes ayant le même :
        // teacher_id + class_id + subject_id + course_start_date
        DB::statement('
            DELETE FROM cahier_de_texte c1
            USING cahier_de_texte c2
            WHERE c1.id > c2.id
              AND c1.teacher_id = c2.teacher_id
              AND c1.class_id = c2.class_id
              AND c1.subject_id = c2.subject_id
              AND c1.course_start_date = c2.course_start_date
        ');

        // 3. Maintenant que les doublons ont été supprimés,
        // créer la contrainte UNIQUE.
        Schema::table('cahier_de_texte', function (Blueprint $table) {
            $table->unique(
                [
                    'teacher_id',
                    'class_id',
                    'subject_id',
                    'course_start_date',
                ],
                'cdt_unique_slot'
            );
        });
    }

    public function down(): void
    {
        Schema::table('cahier_de_texte', function (Blueprint $table) {
            $table->dropUnique('cdt_unique_slot');
            $table->dropUnique(['idempotency_key']);
            $table->dropColumn('idempotency_key');
        });
    }
};
