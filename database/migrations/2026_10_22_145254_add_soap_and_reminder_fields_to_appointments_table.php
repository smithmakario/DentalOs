<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->text('soap_subjective')->nullable()->after('notes');
            $table->text('soap_objective')->nullable()->after('soap_subjective');
            $table->text('soap_assessment')->nullable()->after('soap_objective');
            $table->text('soap_plan')->nullable()->after('soap_assessment');
            $table->timestamp('reminder_24h_sent_at')->nullable()->after('completed_at');
            $table->timestamp('reminder_1h_sent_at')->nullable()->after('reminder_24h_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn([
                'soap_subjective',
                'soap_objective',
                'soap_assessment',
                'soap_plan',
                'reminder_24h_sent_at',
                'reminder_1h_sent_at',
            ]);
        });
    }
};
