<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenant_users', function (Blueprint $table) {
            $table->string('google_id')->nullable()->after('email');
            $table->unique(['tenant_id', 'google_id']);
        });
    }

    public function down(): void
    {
        Schema::table('tenant_users', function (Blueprint $table) {
            $table->dropUnique(['tenant_id', 'google_id']);
            $table->dropColumn('google_id');
        });
    }
};
