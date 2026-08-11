<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StorePatientDocumentRequest;
use App\Models\Patient;
use App\Models\PatientDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PatientDocumentController extends Controller
{
    public function store(StorePatientDocumentRequest $request, Patient $patient): RedirectResponse
    {
        $this->authorize('create', PatientDocument::class);

        $uploadedFile = $request->file('file');

        $path = $uploadedFile->store("patient-documents/{$patient->id}", 'local');

        $patient->documents()->create([
            'uploaded_by' => $request->user('staff')->id,
            'category' => $request->validated('category'),
            'title' => $request->validated('title'),
            'description' => $request->validated('description'),
            'recorded_at' => $request->validated('recorded_at'),
            'file_path' => $path,
            'file_name' => $uploadedFile->getClientOriginalName(),
            'mime_type' => $uploadedFile->getMimeType() ?? 'application/octet-stream',
            'file_size' => $uploadedFile->getSize() ?? 0,
        ]);

        return redirect()
            ->route('tenant.patients.show', $patient)
            ->with('success', __('Document uploaded successfully.'));
    }

    public function download(Patient $patient, PatientDocument $patient_document): StreamedResponse
    {
        $this->authorize('view', $patient_document);

        abort_unless($patient_document->patient_id === $patient->id, 404);

        return Storage::disk('local')->download(
            $patient_document->file_path,
            $patient_document->file_name,
            ['Content-Type' => $patient_document->mime_type],
        );
    }

    public function destroy(Patient $patient, PatientDocument $patient_document): RedirectResponse
    {
        $this->authorize('delete', $patient_document);

        abort_unless($patient_document->patient_id === $patient->id, 404);

        $patient_document->deleteStoredFile();
        $patient_document->delete();

        return redirect()
            ->route('tenant.patients.show', $patient)
            ->with('success', __('Document deleted successfully.'));
    }
}
