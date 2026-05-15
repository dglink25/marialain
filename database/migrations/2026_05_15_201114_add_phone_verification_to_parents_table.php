<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{
    public function up(): void{
        Schema::table('parents', function (Blueprint $table) {
            $table->boolean('is_verifie_phone')->default(false)->after('phone');
            $table->timestamp('verifie_phone_at')->nullable()->after('is_verifie_phone');
        });
    }

    public function down(): void{
        Schema::table('parents', function (Blueprint $table) {
            $table->dropColumn(['is_verifie_phone', 'verifie_phone_at']);
        });
    }
};