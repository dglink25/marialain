<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('primaire_sommative_evaluations', function (Blueprint $table) {
            $table->string('titre')->nullable()->after('note_max');
            $table->date('date_evaluation')->nullable()->after('titre');
        });
    }

    public function down(): void
    {
        Schema::table('primaire_sommative_evaluations', function (Blueprint $table) {
            $table->dropColumn(['titre', 'date_evaluation']);
        });
    }
};
