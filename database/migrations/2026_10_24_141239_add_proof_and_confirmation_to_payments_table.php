<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('proof_path')->nullable()->after('notes');
            $table->timestamp('confirmed_at')->nullable()->after('proof_path');
            $table->foreignId('confirmed_by')->nullable()->after('confirmed_at')->constrained('tenant_users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('confirmed_by');
            $table->dropColumn(['proof_path', 'confirmed_at']);
        });
    }
};
