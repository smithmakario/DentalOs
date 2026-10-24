<?php

namespace Tests\Feature\Tenant;

use App\Enums\PlatformRole;
use App\Models\Organization;
use App\Models\Tenant;
use App\Models\User;
use App\Services\ClinicOnboardingService;
use App\Services\TenantDatabaseCleanup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class OrphanTenantDatabaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_branch_reuses_orphan_sqlite_database_file(): void
    {
        $branchSlug = 'orphan-branch-'.fake()->unique()->lexify('????');
        $databaseName = config('tenancy.database.prefix').$branchSlug;
        $databasePath = database_path($databaseName);

        File::put($databasePath, '');

        $this->assertTrue(File::exists($databasePath));

        $organization = Organization::factory()->create([
            'is_active' => false,
        ]);

        $tenant = app(ClinicOnboardingService::class)->addBranch($organization, [
            'branch_name' => 'Orphan Branch',
            'branch_slug' => $branchSlug,
            'contact_email' => 'contact@orphan.test',
        ]);

        $this->assertSame($branchSlug, $tenant->id);
        $this->assertDatabaseHas('tenants', [
            'id' => $branchSlug,
            'organization_id' => $organization->id,
        ]);
        $this->assertTrue(File::exists($databasePath));

        File::delete($databasePath);
        $tenant->database()->manager()->deleteDatabase($tenant);
    }

    public function test_cleanup_drops_orphan_sqlite_databases_without_tenant_rows(): void
    {
        $branchSlug = 'leftover-'.fake()->unique()->lexify('????');
        $databaseName = config('tenancy.database.prefix').$branchSlug;
        $databasePath = database_path($databaseName);

        File::put($databasePath, '');

        $this->assertDatabaseMissing('tenants', ['id' => $branchSlug]);

        $dropped = app(TenantDatabaseCleanup::class)->dropOrphanDatabases();

        $this->assertContains($databaseName, $dropped);
        $this->assertFalse(File::exists($databasePath));
    }

    public function test_super_admin_can_create_clinic_when_orphan_database_exists(): void
    {
        $suffix = fake()->unique()->lexify('????');
        $branchSlug = 'healing-stream-'.$suffix;
        $databaseName = config('tenancy.database.prefix').$branchSlug;

        File::put(database_path($databaseName), '');

        $user = User::factory()->create([
            'platform_role' => PlatformRole::SuperAdmin,
        ]);

        $response = $this->actingAs($user)->post(route('clinics.store'), [
            'name' => 'Healing Stream '.$suffix,
            'type' => 'single',
            'email' => 'contact@healing-'.$suffix.'.test',
            'branch_name' => 'Healing Stream '.$suffix.' - Main',
            'branch_slug' => $branchSlug,
            'admin_name' => 'Clinic Admin',
            'admin_email' => 'admin@healing-'.$suffix.'.test',
            'admin_password' => 'password',
        ]);

        $organization = Organization::query()->where('email', 'contact@healing-'.$suffix.'.test')->firstOrFail();

        $response->assertRedirect(route('clinics.show', $organization));
        $this->assertDatabaseHas('tenants', [
            'id' => $branchSlug,
            'organization_id' => $organization->id,
        ]);

        $tenant = Tenant::query()->findOrFail($branchSlug);
        $tenant->database()->manager()->deleteDatabase($tenant);
    }
}
