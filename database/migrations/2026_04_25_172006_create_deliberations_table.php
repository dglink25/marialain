<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{
    public function up(): void {
        Schema::create('deliberations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_class_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('source_academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->foreignId('target_class_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('target_academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->foreignId('deliberated_by')->constrained('users')->onDelete('cascade');
            $table->boolean('keep_timetable')->default(false);
            $table->integer('passed_count')->default(0);
            $table->integer('repeated_count')->default(0);
            $table->timestamp('deliberated_at')->nullable();
            $table->boolean('is_cancelled')->default(false);
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('deliberation_students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('deliberation_id')->constrained('deliberations')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            // Snapshot des données AVANT délibération
            $table->foreignId('old_class_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('old_academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->string('old_registration_type')->nullable();
            // Données APRÈS délibération
            $table->foreignId('new_class_id')->nullable()->constrained('classes')->onDelete('set null');
            $table->foreignId('new_academic_year_id')->nullable()->constrained('academic_years')->onDelete('set null');
            $table->string('new_registration_type')->nullable();
            // Statut
            $table->enum('status', ['passed', 'repeated'])->default('passed');
            // Moyennes au moment de la délibération (snapshot)
            $table->decimal('annual_average', 5, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void{
        Schema::dropIfExists('deliberation_students');
        Schema::dropIfExists('deliberations');
    }
};