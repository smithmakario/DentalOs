<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->string('preferred_payment_method')->nullable()->after('emergency_contact_phone');
            $table->string('hmo_plan')->nullable()->after('insurance_number');
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn(['preferred_payment_method', 'hmo_plan']);
        });
    }
};
