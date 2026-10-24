<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * HR tables belong on the central connection (they FK to staff_members).
 * This tenant migration is retained as a no-op so existing migration
 * histories stay consistent, and to clean up any partial tables from
 * the previous incorrect placement.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('staff_performance_reviews');
        Schema::dropIfExists('staff_leave_requests');
        Schema::dropIfExists('staff_attendances');
    }

    public function down(): void
    {
        //
    }
};
