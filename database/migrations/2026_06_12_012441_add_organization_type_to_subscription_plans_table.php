<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->string('organization_type')->default('single')->after('slug');
        });

        DB::table('subscription_plans')
            ->where('slug', 'enterprise')
            ->update(['organization_type' => 'dso']);

        DB::table('subscription_plans')
            ->whereIn('slug', ['essential', 'professional'])
            ->update(['organization_type' => 'single']);
    }

    public function down(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->dropColumn('organization_type');
        });
    }
};
