<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffPerformanceReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'staff_member_id',
        'reviewer_id',
        'review_date',
        'rating',
        'comments',
        'productivity_score',
    ];

    protected function casts(): array
    {
        return [
            'review_date' => 'date',
            'rating' => 'integer',
            'productivity_score' => 'decimal:2',
        ];
    }

    public function staffMember(): BelongsTo
    {
        return $this->belongsTo(StaffMember::class, 'staff_member_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(StaffMember::class, 'reviewer_id');
    }
}
