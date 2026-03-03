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
        'quantity' => 'decimal:6',
        'avg_buy_price' => 'decimal:2',
        'current_price' => 'decimal:2',
        'last_updated' => 'date',
    ];

    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    // Computed attributes

    public function getMarketValueAttribute()
    {
        return $this->quantity * $this->current_price;
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
