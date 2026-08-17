<?php

namespace Tests\Feature;

use App\Models\Patient;
use Tests\TenantTestCase;

class PatientApiTest extends TenantTestCase
{
    // ──────────────────────────────────────────────────────────
    // Authentication
    // ──────────────────────────────────────────────────────────

    public function test_patient_can_login_with_valid_credentials(): void
    {
        $this->tenant->run(function (): void {
            Patient::factory()->create([
                'email' => 'ada@example.com',
                'password' => 'secret123',
            ]);
        });

        $response = $this->postJson(
            $this->tenantUrl('/api/login'),
            ['email' => 'ada@example.com', 'password' => 'secret123']
        );

        $response->assertOk()
            ->assertJsonStructure(['patient', 'token'])
            ->assertJsonPath('patient.email', 'ada@example.com');
    }

    public function test_patient_login_fails_with_wrong_password(): void
    {
        $this->tenant->run(function (): void {
            Patient::factory()->create([
                'email' => 'john@example.com',
                'password' => 'correct-password',
            ]);
        });

        $response = $this->postJson(
            $this->tenantUrl('/api/login'),
            ['email' => 'john@example.com', 'password' => 'wrong-password']
        );

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_patient_login_fails_with_non_existent_email(): void
    {
        $response = $this->postJson(
            $this->tenantUrl('/api/login'),
            ['email' => 'ghost@example.com', 'password' => 'anything']
        );

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_patient_login_requires_email_and_password(): void
    {
        $response = $this->postJson($this->tenantUrl('/api/login'), []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email', 'password']);
    }

    // ──────────────────────────────────────────────────────────
    // Dashboard
    // ──────────────────────────────────────────────────────────

    public function test_authenticated_patient_can_access_dashboard(): void
    {
        $patient = $this->tenant->run(function (): Patient {
            return Patient::factory()->create([
                'email' => 'patient@example.com',
                'password' => 'secret',
            ]);
        });

        $token = $this->tenant->run(fn (): string => $patient->createToken('test')->plainTextToken);

        $response = $this->withToken($token)->getJson($this->tenantUrl('/api/dashboard'));

        $response->assertOk()
            ->assertJsonStructure(['patient']);
    }

    public function test_unauthenticated_patient_cannot_access_dashboard(): void
    {
        $response = $this->getJson($this->tenantUrl('/api/dashboard'));

        $response->assertUnauthorized();
    }

    public function test_dashboard_returns_correct_patient_data(): void
    {
        $patient = $this->tenant->run(function (): Patient {
            return Patient::factory()->create([
                'email' => 'check@example.com',
                'password' => 'secret',
                'first_name' => 'Chidi',
                'last_name' => 'Obi',
            ]);
        });

        $token = $this->tenant->run(fn (): string => $patient->createToken('test')->plainTextToken);

        $response = $this->withToken($token)->getJson($this->tenantUrl('/api/dashboard'));

        $response->assertOk()
            ->assertJsonPath('patient.first_name', 'Chidi')
            ->assertJsonPath('patient.last_name', 'Obi')
            ->assertJsonMissingPath('patient.password');
    }

    public function test_one_patient_token_cannot_access_another_patients_data(): void
    {
        [$patientA, $patientB] = $this->tenant->run(function (): array {
            return [
                Patient::factory()->create(['email' => 'a@example.com', 'password' => 'secret']),
                Patient::factory()->create(['email' => 'b@example.com', 'password' => 'secret']),
            ];
        });

        // Token for patient A should return patient A's data, not B's
        $tokenA = $this->tenant->run(fn (): string => $patientA->createToken('test')->plainTextToken);

        $response = $this->withToken($tokenA)->getJson($this->tenantUrl('/api/dashboard'));

        $response->assertOk()
            ->assertJsonPath('patient.email', 'a@example.com');
    }
}
