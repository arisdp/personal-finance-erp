<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasAuditTrail;

class AssetHolding extends Model
{
    use HasUuids, SoftDeletes, HasAuditTrail;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'workspace_id',
        'instrument_id',
        'account_id',
        'asset_type',
        'ticker',
        'asset_name',
        'quantity',
        'avg_buy_price',
        'current_price',
        'last_updated',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'quantity'      => 'decimal:6',
        'avg_buy_price' => 'decimal:2',
        'current_price' => 'decimal:2',
        'last_updated'  => 'date',
    ];

    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function instrument()
    {
        return $this->belongsTo(InvestmentInstrument::class, 'instrument_id');
    }

    // Computed attributes

    /**
     * Effective price: uses instrument price if linked, else stored current_price.
     */
    public function getEffectivePriceAttribute()
    {
        if ($this->instrument_id && $this->relationLoaded('instrument') && $this->instrument) {
            return (float) $this->instrument->current_price;
        }
        if ($this->instrument_id && $this->instrument) {
            return (float) $this->instrument->current_price;
        }
        return (float) $this->current_price;
    }

    public function getMarketValueAttribute()
    {
        return $this->quantity * $this->effective_price;
    }

    public function getCostBasisAttribute()
    {
        return $this->quantity * $this->avg_buy_price;
    }

    public function getUnrealizedGainLossAttribute()
    {
        return $this->market_value - $this->cost_basis;
    }

    public function getGainLossPercentageAttribute()
    {
        if ($this->cost_basis <= 0) return 0;
        return round(($this->unrealized_gain_loss / $this->cost_basis) * 100, 2);
    }

    public function getIsProfitAttribute()
    {
        return $this->unrealized_gain_loss >= 0;
    }
}
