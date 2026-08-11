<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organization_registration_requests', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('location');
            $table->string('government_approval');
            $table->string('contact_person');
            $table->string('email');
            $table->string('phone', 30);
            $table->string('status')->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->string('onboarding_token', 64)->nullable()->unique();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('organization_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index('status');
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organization_registration_requests');
    }
};
