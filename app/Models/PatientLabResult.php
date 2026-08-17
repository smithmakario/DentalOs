<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientLabResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'test_name',
        'test_date',
        'result',
        'reference_range',
        'document_id',
    ];

    protected function casts(): array
    {
        return [
            'test_date' => 'date',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(PatientDocument::class, 'document_id');
    }
}
