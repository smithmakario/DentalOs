<?php

namespace Database\Seeders;

use App\Enums\AppointmentStatus;
use App\Enums\BillingCycle;
use App\Enums\OrganizationRole;
use App\Enums\OrganizationType;
use App\Enums\PlatformRole;
use App\Enums\StaffRole;
use App\Models\Appointment;
use App\Models\BranchProfile;
use App\Models\Organization;
use App\Models\Patient;
use App\Models\Staff;
use App\Models\StaffMember;
use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use App\Models\User;
use App\Services\ClinicSubscriptionAccess;
use App\Services\StaffProvisioningService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Local/demo data only. Not run by default — use:
 * php artisan db:seed --class=DemoClinicSeeder
 */
class DemoClinicSeeder extends Seeder
{
    public function run(): void
    {
        $organization = Organization::query()->updateOrCreate(
            ['slug' => 'smile-dental'],
            [
                'name' => 'Smile Dental Group',
                'type' => OrganizationType::Dso,
                'email' => 'contact@smiledental.test',
                'is_active' => true,
            ],
        );

        $orgAdmin = User::query()->updateOrCreate(
            ['email' => 'org@smiledental.test'],
            [
                'name' => 'Smile Org Admin',
                'password' => Hash::make('password'),
                'platform_role' => PlatformRole::OrgAdmin,
                'email_verified_at' => now(),
            ],
        );

        $organization->users()->syncWithoutDetaching([
            $orgAdmin->id => ['role' => OrganizationRole::Owner->value],
        ]);

        $plan = SubscriptionPlan::query()->firstOrCreate(
            ['slug' => 'demo-enterprise'],
            [
                'name' => 'Demo Enterprise',
                'organization_type' => OrganizationType::Dso,
                'description' => 'Demo plan for local seeding.',
                'price_monthly' => 99000,
                'price_yearly' => 990000,
                'is_active' => true,
                'sort_order' => 0,
            ],
        );

        app(ClinicSubscriptionAccess::class)->assignPlan(
            $organization,
            $plan,
            BillingCycle::Monthly,
        );

        $tenant = $this->createDemoTenant(
            id: 'downtown',
            organization: $organization,
            name: 'Downtown Branch',
            domain: 'downtown.localhost',
        );

        $uptown = $this->createDemoTenant(
            id: 'uptown',
            organization: $organization,
            name: 'Uptown Branch',
            domain: 'uptown.localhost',
        );

        $branchAdmin = StaffMember::query()->updateOrCreate(
            [
                'organization_id' => $organization->id,
                'email' => 'admin@downtown.test',
            ],
            [
                'first_name' => 'Branch',
                'last_name' => 'Admin',
                'phone' => '+1 555 0101',
                'role' => StaffRole::ClinicAdmin,
                'password' => Hash::make('password'),
                'has_global_branch_access' => true,
                'is_active' => true,
            ],
        );

        $dentist = StaffMember::query()->updateOrCreate(
            [
                'organization_id' => $organization->id,
                'email' => 'dentist@downtown.test',
            ],
            [
                'first_name' => 'Jane',
                'last_name' => 'Doe',
                'phone' => '+1 555 0102',
                'role' => StaffRole::Dentist,
                'specialization' => 'General Dentistry',
                'license_number' => 'DDS-12345',
                'password' => Hash::make('password'),
                'has_global_branch_access' => false,
                'is_active' => true,
            ],
        );

        $specialist = StaffMember::query()->updateOrCreate(
            [
                'organization_id' => $organization->id,
                'email' => 'specialist@smiledental.test',
            ],
            [
                'first_name' => 'Alex',
                'last_name' => 'Rivera',
                'phone' => '+1 555 0103',
                'role' => StaffRole::Dentist,
                'specialization' => 'Orthodontics',
                'license_number' => 'DDS-67890',
                'password' => Hash::make('password'),
                'has_global_branch_access' => false,
                'is_active' => true,
            ],
        );

        $provisioning = app(StaffProvisioningService::class);
        $provisioning->sync($branchAdmin);
        $provisioning->storeAssignments($dentist, [$tenant->id]);
        $provisioning->storeAssignments($specialist, [$tenant->id, $uptown->id]);

        $tenant->run(function () {
            BranchProfile::query()->updateOrCreate(
                ['id' => 1],
                [
                    'name' => 'Downtown Branch',
                    'contact_email' => 'downtown@smiledental.test',
                    'phone' => '+1 555 0100',
                    'timezone' => 'America/New_York',
                ],
            );

            Patient::query()->updateOrCreate(
                ['email' => 'alice.johnson@example.com'],
                [
                    'first_name' => 'Alice',
                    'last_name' => 'Johnson',
                    'phone' => '+1 555 0201',
                    'date_of_birth' => '1988-04-12',
                    'gender' => 'female',
                    'insurance_provider' => 'Delta Dental',
                    'insurance_number' => 'DD-88421',
                    'is_active' => true,
                ],
            );

            Patient::query()->updateOrCreate(
                ['email' => 'bob.williams@example.com'],
                [
                    'first_name' => 'Bob',
                    'last_name' => 'Williams',
                    'phone' => '+1 555 0202',
                    'date_of_birth' => '1975-11-03',
                    'gender' => 'male',
                    'medical_notes' => 'Penicillin allergy.',
                    'is_active' => true,
                ],
            );

            $dentist = Staff::query()->where('email', 'dentist@downtown.test')->first();
            $alice = Patient::query()->where('email', 'alice.johnson@example.com')->first();
            $bob = Patient::query()->where('email', 'bob.williams@example.com')->first();

            if ($dentist && $alice) {
                Appointment::query()->firstOrCreate(
                    [
                        'patient_id' => $alice->id,
                        'scheduled_at' => now()->addDay()->setTime(9, 0),
                    ],
                    [
                        'provider_id' => $dentist->id,
                        'title' => 'Routine Cleaning',
                        'duration_minutes' => 30,
                        'status' => AppointmentStatus::Confirmed,
                    ],
                );
            }

            if ($dentist && $bob) {
                Appointment::query()->firstOrCreate(
                    [
                        'patient_id' => $bob->id,
                        'scheduled_at' => now()->addDays(2)->setTime(14, 30),
                    ],
                    [
                        'provider_id' => $dentist->id,
                        'title' => 'Crown Consultation',
                        'duration_minutes' => 45,
                        'status' => AppointmentStatus::Scheduled,
                        'notes' => 'Review X-rays from prior visit.',
                    ],
                );
            }
        });

        $this->command?->info('Demo clinic data seeded (Smile Dental / downtown / uptown).');
    }

    private function createDemoTenant(
        string $id,
        Organization $organization,
        string $name,
        string $domain,
    ): Tenant {
        $existing = Tenant::query()->find($id);

        if ($existing !== null) {
            Tenant::withoutEvents(function () use ($existing): void {
                $existing->domains()->delete();
                $existing->delete();
            });
        }

        $databaseName = config('tenancy.database.prefix').$id.config('tenancy.database.suffix');
        DB::statement('DROP DATABASE IF EXISTS `'.$databaseName.'`');

        $tenant = Tenant::query()->create([
            'id' => $id,
            'organization_id' => $organization->id,
            'name' => $name,
            'slug' => $id,
            'is_active' => true,
        ]);

        $tenant->domains()->create(['domain' => $domain]);

        return $tenant;
    }
}
