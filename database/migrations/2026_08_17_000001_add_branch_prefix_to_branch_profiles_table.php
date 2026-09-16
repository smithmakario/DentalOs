<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('branch_profiles', function (Blueprint $table) {
            $table->string('branch_prefix', 10)->nullable()->after('name')->unique();
        });
    }

    public function down(): void
    {
        Schema::table('branch_profiles', function (Blueprint $table) {
            $table->dropColumn('branch_prefix');
        });
    }
};
