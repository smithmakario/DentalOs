<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('treatment_plan_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('treatment_plan_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->decimal('estimated_total', 12, 2)->default(0);
            $table->boolean('is_selected')->default(false);
            $table->timestamp('consent_signed_at')->nullable();
            $table->string('consent_signer_name')->nullable();
            $table->string('consent_signature_path')->nullable();
            $table->text('consent_statement')->nullable();
            $table->foreignId('consent_witnessed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['treatment_plan_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('treatment_plan_options');
    }
};
