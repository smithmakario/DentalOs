<?php

namespace App\Models;

use App\Enums\PatientDocumentCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PatientDocument extends Model
{
    /** @use HasFactory<\Database\Factories\PatientDocumentFactory> */
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'uploaded_by',
        'category',
        'title',
        'description',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
        'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'category' => PatientDocumentCategory::class,
            'file_size' => 'integer',
            'recorded_at' => 'date',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'uploaded_by');
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    public function formattedFileSize(): string
    {
        if ($this->file_size >= 1_048_576) {
            return number_format($this->file_size / 1_048_576, 1).' MB';
        }

        if ($this->file_size >= 1024) {
            return number_format($this->file_size / 1024, 1).' KB';
        }

        return $this->file_size.' B';
    }

    public function deleteStoredFile(): void
    {
        Storage::disk('local')->delete($this->file_path);
    }
}
