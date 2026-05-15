<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{
    public function up(): void{
        Schema::table('parents', function (Blueprint $table) {
            $table->string('phone_otp', 5)->nullable()->after('verifie_phone_at');
            $table->timestamp('phone_otp_expires_at')->nullable()->after('phone_otp');
            $table->timestamp('phone_otp_sent_at')->nullable()->after('phone_otp_expires_at');
        });
    }

    public function down(): void {
        Schema::table('parents', function (Blueprint $table) {
            $table->dropColumn([
                'phone_otp',
                'phone_otp_expires_at',
                'phone_otp_sent_at',
            ]);
        });
    }
};