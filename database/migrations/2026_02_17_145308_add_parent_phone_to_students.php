<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('students', function (Blueprint $table) {

            // Si la colonne n'existe pas → on la crée
            if (!Schema::hasColumn('students', 'parent_phone')) {
                $table->string('parent_phone')->nullable();
            } else {
                // Si elle existe → on la modifie
                $table->string('parent_phone')->nullable()->change();
            }

            // Ajouter l'index (Laravel évite le doublon si déjà existant)
            $table->index('parent_phone');
        });
    }

    public function down()
    {
        Schema::table('students', function (Blueprint $table) {

            if (Schema::hasColumn('students', 'parent_phone')) {
                $table->dropIndex(['parent_phone']);
                // Optionnel :
                // $table->dropColumn('parent_phone');
            }
        });
    }
};
