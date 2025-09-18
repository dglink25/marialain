<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->date('birth_date');
            $table->integer('age')->nullable();
            $table->foreignId('entity_id')->constrained('entities')->onDelete('cascade');
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->string('vaccination_card')->nullable(); // PDF
            $table->string('birth_certificate'); // PDF
            $table->string('previous_report_card')->nullable(); // PDF
            $table->string('diploma_certificate')->nullable(); // PDF
            $table->string('parent_full_name');
            $table->string('parent_email');
            $table->integer('school_fees');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
