<?php

use App\Models\TreatmentPlan;
use App\Models\TreatmentPlanItem;
use App\Models\TreatmentPlanOption;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('treatment_plan_items', function (Blueprint $table) {
            $table->foreignId('treatment_plan_option_id')->nullable()->after('treatment_plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('clinic_service_id')->nullable()->after('treatment_plan_option_id')->constrained()->nullOnDelete();
            $table->string('phase_name')->nullable()->after('surface');
            $table->unsignedSmallInteger('phase_order')->default(0)->after('phase_name');
        });

        TreatmentPlan::query()->with('items')->each(function (TreatmentPlan $plan): void {
            if ($plan->items->isEmpty()) {
                return;
            }

            $option = TreatmentPlanOption::query()->create([
                'treatment_plan_id' => $plan->id,
                'name' => __('Option A'),
                'sort_order' => 0,
                'estimated_total' => $plan->estimated_total,
                'is_selected' => true,
            ]);

            TreatmentPlanItem::query()
                ->where('treatment_plan_id', $plan->id)
                ->update([
                    'treatment_plan_option_id' => $option->id,
                    'phase_name' => __('Phase 1'),
                    'phase_order' => 0,
                ]);
        });

        Schema::table('treatment_plan_items', function (Blueprint $table) {
            $table->dropForeign(['treatment_plan_id']);
            $table->dropColumn('treatment_plan_id');
        });
    }

    public function down(): void
    {
        Schema::table('treatment_plan_items', function (Blueprint $table) {
            $table->foreignId('treatment_plan_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
        });

        TreatmentPlanOption::query()->with('items')->each(function (TreatmentPlanOption $option): void {
            TreatmentPlanItem::query()
                ->where('treatment_plan_option_id', $option->id)
                ->update(['treatment_plan_id' => $option->treatment_plan_id]);
        });

        Schema::table('treatment_plan_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('treatment_plan_option_id');
            $table->dropConstrainedForeignId('clinic_service_id');
            $table->dropColumn(['phase_name', 'phase_order']);
        });
    }
};
