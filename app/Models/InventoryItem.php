<?php

namespace App\Models;

use App\Enums\InventoryItemType;
use Illuminate\Database\Eloquent\Builder;
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
        'current_stock_level',
        'expiry_date',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'type' => InventoryItemType::class,
            'min_stock_level' => 'decimal:2',
            'current_stock_level' => 'decimal:2',
            'expiry_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(InventoryCategory::class, 'inventory_category_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    public function scopeLowStock(Builder $query): void
    {
        $query->whereColumn('current_stock_level', '<=', 'min_stock_level')
              ->where('current_stock_level', '>', 0)
              ->where('is_active', true);
    }

    public function scopeOutOfStock(Builder $query): void
    {
        $query->where('current_stock_level', '<=', 0)
              ->where('is_active', true);
    }

    public function scopeExpiringSoon(Builder $query, int $days = 30): void
    {
        $query->whereNotNull('expiry_date')
              ->where('expiry_date', '<=', now()->addDays($days))
              ->where('expiry_date', '>=', now())
              ->where('is_active', true);
    }

    public function scopeExpired(Builder $query): void
    {
        $query->whereNotNull('expiry_date')
              ->where('expiry_date', '<', now())
              ->where('is_active', true);
    }
}
