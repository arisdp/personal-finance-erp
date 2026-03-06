<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvestmentInstrument extends Model
{
    use HasUuids, SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'ticker',
        'name',
        'asset_type',
        'current_price',
        'last_price_update',
        'notes',
    ];

    protected $casts = [
        'current_price'      => 'decimal:2',
        'last_price_update'  => 'datetime',
    ];

    public function holdings()
    {
        return $this->hasMany(AssetHolding::class, 'instrument_id');
    }

    // Computed aggregates across all holdings

    public function getTotalQuantityAttribute()
    {
        return $this->holdings->sum('quantity');
    }

    public function getTotalCostBasisAttribute()
    {
        return $this->holdings->sum(fn($h) => $h->quantity * $h->avg_buy_price);
    }

    public function getTotalMarketValueAttribute()
    {
        return $this->holdings->sum(fn($h) => $h->quantity * $this->current_price);
    }

    public function getTotalGainLossAttribute()
    {
        return $this->total_market_value - $this->total_cost_basis;
    }

    public function getGainLossPercentageAttribute()
    {
        if ($this->total_cost_basis <= 0) return 0;
        return round(($this->total_gain_loss / $this->total_cost_basis) * 100, 2);
    }

    public function getIsProfitAttribute()
    {
        return $this->total_gain_loss >= 0;
    }
}
