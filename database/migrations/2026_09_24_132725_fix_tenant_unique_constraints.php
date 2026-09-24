<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('clinic_services', function (Blueprint $table) {
            $table->dropUnique('clinic_services_code_unique');
            $table->unique(['tenant_id', 'code']);
        });



        Schema::table('invoices', function (Blueprint $table) {
            $table->dropUnique('invoices_invoice_number_unique');
            $table->unique(['tenant_id', 'invoice_number']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropUnique('payments_payment_number_unique');
            $table->unique(['tenant_id', 'payment_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clinic_services', function (Blueprint $table) {
            $table->dropUnique(['tenant_id', 'code']);
            $table->unique('code');
        });



        Schema::table('invoices', function (Blueprint $table) {
            $table->dropUnique(['tenant_id', 'invoice_number']);
            $table->unique('invoice_number');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropUnique(['tenant_id', 'payment_number']);
            $table->unique('payment_number');
        });
    }
};
