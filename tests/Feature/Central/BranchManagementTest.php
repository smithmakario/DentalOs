<?php

namespace Tests\Feature\Central;

use App\Enums\PlatformRole;
use App\Models\Organization;
use App\Models\User;
use App\Support\BranchIdentifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Concerns\CreatesTenantBranches;
use Tests\TestCase;

class BranchManagementTest extends TestCase
{
    use CreatesTenantBranches;
    use RefreshDatabase;

    protected function tearDown(): void
    {
        $this->tearDownProvisionedTenants();

        if (tenancy()->initialized) {
            tenancy()->end();
        }

        parent::tearDown();
    }

    public function test_branches_index_does_not_prefill_add_branch_form(): void
    {
        $organization = Organization::factory()->create([
            'name' => 'Harbor Dental',
        ]);

        $user = User::factory()->create([
            'platform_role' => PlatformRole::SuperAdmin,
        ]);

        $response = $this->actingAs($user)->get(route('clinics.branches.index', $organization));

        $response->assertOk();
        $response->assertDontSee('createName: '.json_encode(BranchIdentifier::suggestBranchName($organization->name)), false);
        $response->assertSee(__('Add Branch'), false);
    }

    public function test_super_admin_can_update_branch(): void
    {
        $organization = Organization::factory()->create();
        $tenant = $this->createTenantBranch(
            $organization,
            'main-'.Str::lower(fake()->unique()->lexify('????')),
            'Main Branch',
        );

        $user = User::factory()->create([
            'platform_role' => PlatformRole::SuperAdmin,
        ]);

        $response = $this->actingAs($user)->patch(route('clinics.branches.update', [$organization, $tenant]), [
            'branch_name' => 'Downtown Branch',
            'is_active' => 0,
        ]);

        $response->assertRedirect(route('clinics.branches.index', $organization));

        $this->assertDatabaseHas('tenants', [
            'id' => $tenant->id,
            'name' => 'Downtown Branch',
            'is_active' => 0,
        ]);
    }

    public function test_super_admin_can_delete_branch_when_more_than_one_exists(): void
    {
        $organization = Organization::factory()->create();
        $keep = $this->createTenantBranch(
            $organization,
            'keep-'.Str::lower(fake()->unique()->lexify('????')),
            'Keep Branch',
        );
        $remove = $this->createTenantBranch(
            $organization,
            'remove-'.Str::lower(fake()->unique()->lexify('????')),
            'Remove Branch',
        );

        $user = User::factory()->create([
            'platform_role' => PlatformRole::SuperAdmin,
        ]);

        $response = $this->actingAs($user)->delete(route('clinics.branches.destroy', [$organization, $remove]));

        $response->assertRedirect(route('clinics.branches.index', $organization));
        $this->assertDatabaseMissing('tenants', ['id' => $remove->id]);
        $this->assertDatabaseHas('tenants', ['id' => $keep->id]);
    }

    public function test_cannot_delete_the_last_branch(): void
    {
        $organization = Organization::factory()->create();
        $only = $this->createTenantBranch(
            $organization,
            'only-'.Str::lower(fake()->unique()->lexify('????')),
            'Only Branch',
        );

        $user = User::factory()->create([
            'platform_role' => PlatformRole::SuperAdmin,
        ]);

        $response = $this->actingAs($user)->delete(route('clinics.branches.destroy', [$organization, $only]));

        $response->assertRedirect(route('clinics.branches.index', $organization));
        $response->assertSessionHasErrors('branch');
        $this->assertDatabaseHas('tenants', ['id' => $only->id]);
    }
}
