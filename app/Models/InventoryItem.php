<?php

namespace App\Models;

use App\Enums\InventoryItemType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_category_id',
        'name',
        'sku',
        'type',
        'unit_measure',
        'min_stock_level',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'type' => InventoryItemType::class,
            'min_stock_level' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(InventoryCategory::class, 'inventory_category_id');
    }

    public function batches(): HasMany
    {
        return $this->hasMany(InventoryBatch::class);
    }

    public function activeBatches(): HasMany
    {
        return $this->hasMany(InventoryBatch::class)->where('is_active', true)->where('current_quantity', '>', 0);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    /**
     * Compute the total stock level from all active batches.
     */
    protected function currentStockLevel(): Attribute
    {
        return Attribute::get(fn () => (float) $this->activeBatches()->sum('current_quantity'));
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Items where total active batch stock is <= min_stock_level but > 0.
     */
    public function scopeLowStock(Builder $query): void
    {
        $query->active()
            ->whereHas('activeBatches')
            ->withSum('activeBatches as total_stock', 'current_quantity')
            ->havingRaw('total_stock <= min_stock_level AND total_stock > 0');
    }

    /**
     * Items with no stock across all batches.
     */
    public function scopeOutOfStock(Builder $query): void
    {
        $query->active()
            ->whereDoesntHave('activeBatches');
    }

    /**
     * Items with at least one batch expiring within $days.
     */
    public function scopeWithExpiringBatches(Builder $query, int $days = 30): void
    {
        $query->active()
            ->whereHas('batches', function (Builder $q) use ($days): void {
                $q->whereNotNull('expiry_date')
                    ->where('expiry_date', '<=', now()->addDays($days))
                    ->where('expiry_date', '>=', now())
                    ->where('current_quantity', '>', 0);
            });
    }
}
