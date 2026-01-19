<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{
    public function up(){
        Schema::table('cahier_de_texte', function (Blueprint $table) {
            $table->boolean('is_validated')->default(false);
            $table->timestamp('validated_at')->nullable();
            $table->foreignId('validated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('validation_notes')->nullable();
        });
    }

    public function down() {
        Schema::table('cahier_de_texte', function (Blueprint $table) {
            $table->dropColumn(['is_validated', 'validated_at', 'validated_by', 'validation_notes']);
        });
    }
};