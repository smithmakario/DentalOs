<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_member_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->timestamp('clock_in_time')->nullable();
            $table->timestamp('clock_out_time')->nullable();
            $table->string('status')->default('present'); // present, absent, late
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('staff_leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_member_id')->constrained()->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('leave_type'); // sick, annual, unpaid
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->text('reason')->nullable();
            $table->timestamps();
        });

        Schema::create('staff_performance_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_member_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reviewer_id')->constrained('staff_members')->cascadeOnDelete();
            $table->date('review_date');
            $table->integer('rating'); // 1-5
            $table->text('comments')->nullable();
            $table->decimal('productivity_score', 5, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_performance_reviews');
        Schema::dropIfExists('staff_leave_requests');
        Schema::dropIfExists('staff_attendances');
    }
};
