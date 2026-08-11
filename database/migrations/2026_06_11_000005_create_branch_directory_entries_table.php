<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branch_directory_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('tenant_id');
            $table->string('resource_type');
            $table->unsignedBigInteger('local_id');
            $table->string('display_name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->unique(['tenant_id', 'resource_type', 'local_id']);
            $table->index(['organization_id', 'resource_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branch_directory_entries');
    }
};
