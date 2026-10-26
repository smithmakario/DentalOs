<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('branch_profiles', function (Blueprint $table) {
            $table->string('branch_prefix', 10)->nullable()->after('name');
            $table->unique(['tenant_id', 'branch_prefix']);
        });
    }

    public function down(): void
    {
        Schema::table('branch_profiles', function (Blueprint $table) {
            $table->dropUnique(['tenant_id', 'branch_prefix']);
            $table->dropColumn('branch_prefix');
        });
    }
};
