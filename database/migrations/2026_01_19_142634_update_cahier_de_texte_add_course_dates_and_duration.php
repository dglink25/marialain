<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('cahier_de_texte', function (Blueprint $table) {
            $table->dropColumn(['is_late', 'motif_retard', 'duration_minutes']);

            $table->timestamp('course_start_date')->nullable();
            $table->timestamp('course_end_date')->nullable();
        });
    }

    public function down()
    {
        Schema::table('cahier_de_texte', function (Blueprint $table) {
            $table->dropColumn(['course_start_date', 'course_end_date']);

            $table->boolean('is_late')->default(false);
            $table->text('motif_retard')->nullable();
            $table->integer('duration_minutes')->nullable();
        });
    }

};