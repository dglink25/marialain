<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('note_edit_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->string('trimestre');
            $table->string('type'); // ex: I1, I2, D1, D2...
            $table->boolean('is_active')->default(true);
            $table->timestamp('expires_at'); // expiration automatique (2h après autorisation)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('note_edit_permissions');
    }
};
