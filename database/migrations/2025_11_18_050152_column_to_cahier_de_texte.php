<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cahier_de_texte', function (Blueprint $table) {
            if (!Schema::hasColumn('cahier_de_texte', 'is_late')) {
                $table->boolean('is_late')->default(false)->after('content');
            }
            if (!Schema::hasColumn('cahier_de_texte', 'motif_retard')) {
                $table->text('motif_retard')->nullable()->after('is_late');
            }
            if (!Schema::hasColumn('cahier_de_texte', 'duration_minutes')) {
                $table->integer('duration_minutes')->default(0)->after('motif_retard');
            }
            // s'assurer que timestamps existent (created_at, updated_at)
            if (!Schema::hasColumn('cahier_de_texte', 'created_at')) {
                $table->timestamps();
            }
        });
    }

    public function down(): void
    {
        Schema::table('cahier_de_texte', function (Blueprint $table) {
            if (Schema::hasColumn('cahier_de_texte', 'is_late')) {
                $table->dropColumn('is_late');
            }
            if (Schema::hasColumn('cahier_de_texte', 'motif_retard')) {
                $table->dropColumn('motif_retard');
            }
            if (Schema::hasColumn('cahier_de_texte', 'duration_minutes')) {
                $table->dropColumn('duration_minutes');
            }
        });
    }
};
