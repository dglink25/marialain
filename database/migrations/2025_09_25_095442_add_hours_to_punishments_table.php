<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('punishments', function (Blueprint $table) {
            $table->integer('hours')->default(1)->after('reason'); // ajoute la colonne heures
        });
    }

    public function down(): void
    {
        Schema::table('punishments', function (Blueprint $table) {
            $table->dropColumn('hours');
        });
    }
};
