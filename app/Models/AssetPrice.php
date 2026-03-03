<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class AssetPrice extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'account_id',
        'asset_type',
        'ticker',
        'price',
        'price_date',
        'source',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'price_date' => 'date',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Get the latest price for a specific account
     */
    public function scopeLatestPrice($query, $accountId)
    {
        return $query->where('account_id', $accountId)
            ->orderBy('price_date', 'desc')
            ->first();
    }

    /**
     * Get price history for a specific account
     */
    public function scopePriceHistory($query, $accountId, $days = 30)
    {
        return $query->where('account_id', $accountId)
            ->where('price_date', '>=', now()->subDays($days))
            ->orderBy('price_date', 'asc');
    }
}
