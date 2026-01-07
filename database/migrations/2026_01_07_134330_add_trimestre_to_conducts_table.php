<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(){
        Schema::table('conducts', function (Blueprint $table) {
            $table->integer('trimestre')->after('academic_year_id')->default(1);
        });
    }

    public function down(){
        Schema::table('conducts', function (Blueprint $table) {
            $table->dropColumn('trimestre');
        });
    }
};
