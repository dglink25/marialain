<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::table('students', function (Blueprint $table) {
        $table->decimal('school_fees', 10, 2)->nullable()->change();
    });
}

public function down()
{
    Schema::table('students', function (Blueprint $table) {
        $table->decimal('school_fees', 10, 2)->nullable(false)->change();
    });
}

};
