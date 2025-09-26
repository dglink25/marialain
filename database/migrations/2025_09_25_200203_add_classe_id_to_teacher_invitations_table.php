<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('teacher_invitations', function (Blueprint $table) {
            $table->unsignedBigInteger('classe_id')->nullable()->after('academic_year_id');

            // Si la table classes existe déjà et que tu veux clé étrangère
            $table->foreign('classe_id')
                  ->references('id')->on('classes')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('teacher_invitations', function (Blueprint $table) {
            $table->dropForeign(['classe_id']);
            $table->dropColumn('classe_id');
        });
    }
};
