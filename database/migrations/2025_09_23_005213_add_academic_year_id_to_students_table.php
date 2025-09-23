<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->unsignedBigInteger('academic_year_id')->nullable()->after('class_id');

            // Ajouter la clé étrangère si tu as une table academic_years
            $table->foreign('academic_year_id')
                  ->references('id')
                  ->on('academic_years')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['academic_year_id']);
            $table->dropColumn('academic_year_id');
        });
    }
};
