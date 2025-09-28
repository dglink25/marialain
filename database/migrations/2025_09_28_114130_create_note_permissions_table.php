<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('note_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->tinyInteger('trimestre'); // 1, 2, ou 3
            $table->boolean('is_open')->default(false); // true = saisie autorisée
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('note_permissions');
    }
};
