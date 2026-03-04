<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Account extends Model
{
    use HasUuids, SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'code',
        'name',
        'type',
        'parent_id',
        'is_postable',
        'category',
        'credit_limit',
        'track_limit',
        'description',
    ];

    protected $casts = [
        'is_postable' => 'boolean',
        'track_limit' => 'boolean',
        'credit_limit' => 'decimal:2',
    ];

    public function parent()
    {
        return $this->belongsTo(Account::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Account::class, 'parent_id');
    }

    public function journalLines()
    {
        return $this->hasMany(JournalLine::class);
    }

    public function installments()
    {
        return $this->hasMany(Installment::class);
    }

    public function assetPrices()
    {
        return $this->hasMany(AssetPrice::class);
    }

    public function assetHoldings()
    {
        return $this->hasMany(AssetHolding::class);
    }

    public function budgets()
    {
        return $this->hasMany(Budget::class);
    }

    /**
     * Calculate Balance with filters (Supports Date Range)
     */
    public function calculateBalance($workspaceId, $startDate = null, $endDate = null)
    {
        $isDebitNormal = in_array($this->category, ['asset', 'expense']);
        
        $query = $this->journalLines()->whereHas('journalEntry', function($q) use ($workspaceId, $startDate, $endDate) {
            $q->where('workspace_id', $workspaceId);
            
            // If only one date provided, treat as "As Of" (End Date)
            if ($startDate && !$endDate) {
                $q->where('date', '<=', $startDate);
            } 
            // If both provided, treat as range
            elseif ($startDate && $endDate) {
                $q->whereBetween('date', [$startDate, $endDate]);
            }
        });

        if ($isDebitNormal) {
            return (float) $query->sum(DB::raw('debit - credit'));
        }

        return (float) $query->sum(DB::raw('credit - debit'));
    }

    /**
     * Calculate Total Balance (Hierarchical) with filters
     */
    public function calculateTotalBalance($workspaceId, $startDate = null, $endDate = null)
    {
        $balance = $this->calculateBalance($workspaceId, $startDate, $endDate);
        
        foreach ($this->children as $child) {
            $balance += $child->calculateTotalBalance($workspaceId, $startDate, $endDate);
        }

        return $balance;
    }

    // FIRE ACCESSOR: Calculate Balance based on category
    public function getBalanceAttribute()
    {
        return $this->calculateBalance(session('active_workspace_id'));
    }

    // 🔥 Recursive Total Balance (Includes current account + all descendants)
    public function getTotalBalanceAttribute()
    {
        return $this->calculateTotalBalance(session('active_workspace_id'));
    }

    // Credit limit tracking
    public function getUsedLimitAttribute()
    {
        if (!$this->track_limit) return 0;

        $query = $this->journalLines();
        
        if (session()->has('active_workspace_id')) {
            $query->whereHas('journalEntry', function($q) {
                $q->where('workspace_id', session('active_workspace_id'));
            });
        }

        return $query->sum(DB::raw('credit - debit'));
    }

    public function getAvailableLimitAttribute()
    {
        if (!$this->track_limit) return null;

        return $this->credit_limit - $this->used_limit;
    }

    // Latest asset price
    public function getLatestPriceAttribute()
    {
        return $this->assetPrices()
            ->orderBy('price_date', 'desc')
            ->first()?->price ?? 0;
    }
}
