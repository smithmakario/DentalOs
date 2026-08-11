<?php

namespace Tests\Feature\Tenant;

use App\Enums\PatientDocumentCategory;
use App\Enums\StaffRole;
use App\Models\Patient;
use App\Models\PatientDocument;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TenantTestCase;

class PatientDocumentTest extends TenantTestCase
{
    public function test_dentist_can_upload_patient_document(): void
    {
        Storage::fake('local');

        $staff = $this->createStaff(['role' => StaffRole::Dentist]);

        $patient = $this->tenant->run(fn (): Patient => Patient::factory()->create());

        $response = $this->actingAs($staff, 'staff')->post($this->tenantUrl('/patients/'.$patient->id.'/documents'), [
            'category' => PatientDocumentCategory::Xray->value,
            'title' => 'Panoramic X-Ray',
            'description' => 'Initial diagnostic image',
            'recorded_at' => now()->toDateString(),
            'file' => UploadedFile::fake()->image('xray.png', 1200, 800),
        ]);

        $response->assertRedirect($this->tenantUrl('/patients/'.$patient->id));

        $this->tenant->run(function () use ($patient): void {
            $document = PatientDocument::query()->where('patient_id', $patient->id)->firstOrFail();

            $this->assertSame('Panoramic X-Ray', $document->title);
            $this->assertSame(PatientDocumentCategory::Xray, $document->category);
            Storage::disk('local')->assertExists($document->file_path);
        });
    }

    public function test_staff_can_download_patient_document(): void
    {
        Storage::fake('local');

        $staff = $this->createStaff(['role' => StaffRole::Receptionist]);

        $patient = $this->tenant->run(function () use ($staff): Patient {
            $patient = Patient::factory()->create();
            $path = 'patient-documents/'.$patient->id.'/report.pdf';
            Storage::disk('local')->put($path, 'lab-result-content');

            PatientDocument::query()->create([
                'patient_id' => $patient->id,
                'uploaded_by' => $staff->id,
                'category' => PatientDocumentCategory::LabResult,
                'title' => 'Blood Panel',
                'file_path' => $path,
                'file_name' => 'report.pdf',
                'mime_type' => 'application/pdf',
                'file_size' => 18,
            ]);

            return $patient;
        });

        $document = $this->tenant->run(fn () => PatientDocument::query()->firstOrFail());

        $response = $this->actingAs($staff, 'staff')
            ->get($this->tenantUrl('/patients/'.$patient->id.'/documents/'.$document->id.'/download'));

        $response->assertOk();
    }

    public function test_hygienist_cannot_upload_patient_document(): void
    {
        Storage::fake('local');

        $staff = $this->createStaff(['role' => StaffRole::Hygienist]);

        $patient = $this->tenant->run(fn (): Patient => Patient::factory()->create());

        $response = $this->actingAs($staff, 'staff')->post($this->tenantUrl('/patients/'.$patient->id.'/documents'), [
            'category' => PatientDocumentCategory::LabResult->value,
            'title' => 'Lab Result',
            'file' => UploadedFile::fake()->create('result.pdf', 100, 'application/pdf'),
        ]);

        $response->assertForbidden();
    }
}
