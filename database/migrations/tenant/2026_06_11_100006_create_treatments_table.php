<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('treatments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('treatment_plan_item_id')->nullable()->constrained()->nullOnDelete();
            $table->string('procedure_code')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('tooth_code')->nullable();
            $table->string('surface')->nullable();
            $table->decimal('cost', 12, 2)->default(0);
            $table->dateTime('performed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('appointment_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('treatments');
    }
};
