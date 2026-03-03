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
     * Calculate Balance based on category
     * Asset & Expense: Debit (+) - Credit (-)
     * Liability, Equity, Income: Credit (+) - Debit (-)
     */
    public function getBalanceAttribute()
    {
        $isDebitNormal = in_array($this->category, ['asset', 'expense']);
        
        $query = $this->journalLines();
        
        // Scope to active workspace if session exists
        if (session()->has('active_workspace_id')) {
            $query->whereHas('journalEntry', function($q) {
                $q->where('workspace_id', session('active_workspace_id'));
            });
        }

        if ($isDebitNormal) {
            return $query->sum(DB::raw('debit - credit'));
        }

        return $query->sum(DB::raw('credit - debit'));
    }

    // 🔥 Recursive Total Balance (Includes current account + all descendants)
    public function getTotalBalanceAttribute()
    {
        $balance = $this->balance;
        
        foreach ($this->children as $child) {
            $balance += $child->total_balance;
        }

        return $balance;
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
