<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinic_services', function (Blueprint $table) {
            $table->unsignedSmallInteger('duration_minutes')->default(30)->after('price');
            $table->string('icon', 100)->nullable()->after('duration_minutes');
            $table->boolean('is_recommended')->default(false)->after('icon');
        });
    }

    public function down(): void
    {
        Schema::table('clinic_services', function (Blueprint $table) {
            $table->dropColumn(['duration_minutes', 'icon', 'is_recommended']);
        });
    }
};
