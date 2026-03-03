<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasAuditTrail;

class Installment extends Model
{
    use HasUuids, SoftDeletes, HasAuditTrail;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'workspace_id',
        'account_id',
        'name',
        'total_amount',
        'monthly_amount',
        'total_periods',
        'remaining_periods',
        'start_date',
        'end_date',
        'interest_rate',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'monthly_amount' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
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

    public function getPaidPeriodsAttribute()
    {
        return $this->total_periods - $this->remaining_periods;
    }

    public function getPaidAmountAttribute()
    {
        return $this->paid_periods * $this->monthly_amount;
    }

    public function getRemainingAmountAttribute()
    {
        return $this->remaining_periods * $this->monthly_amount;
    }

    public function getProgressPercentageAttribute()
    {
        if ($this->total_periods <= 0) return 0;
        return round(($this->paid_periods / $this->total_periods) * 100, 1);
    }

    public function getIsActiveAttribute()
    {
        return $this->status === 'active';
    }

    public function getIsCompletedAttribute()
    {
        return $this->status === 'completed' || $this->remaining_periods <= 0;
    }

    public function getTotalWithInterestAttribute()
    {
        return $this->monthly_amount * $this->total_periods;
    }

    public function getTotalInterestAttribute()
    {
        return $this->total_with_interest - $this->total_amount;
    }
}
