<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cahier_de_texte', function (Blueprint $table) {

            if (!Schema::hasColumn('cahier_de_texte', 'motif_retard')) {
                $table->text('motif_retard')->nullable()->after('content');
            }

            if (!Schema::hasColumn('cahier_de_texte', 'duration_minutes')) {
                $table->integer('duration_minutes')->default(0)->after('motif_retard');
            }

        });
    }

    public function down(): void
    {
        Schema::table('cahier_de_texte', function (Blueprint $table) {
            if (Schema::hasColumn('cahier_de_texte', 'motif_retard')) {
                $table->dropColumn('motif_retard');
            }
            if (Schema::hasColumn('cahier_de_texte', 'duration_minutes')) {
                $table->dropColumn('duration_minutes');
            }
        });
    }
};
