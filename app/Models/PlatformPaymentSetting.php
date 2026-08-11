<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformPaymentSetting extends Model
{
    protected $fillable = [
        'bank_name',
        'account_name',
        'account_number',
        'bank_code',
        'currency',
        'payment_instructions',
    ];

    public static function current(): ?self
    {
        return static::query()->first();
    }

    public static function currentOrNew(): self
    {
        return static::query()->firstOrNew([]);
    }

    public function isConfigured(): bool
    {
        return filled($this->bank_name)
            && filled($this->account_name)
            && filled($this->account_number);
    }
}
