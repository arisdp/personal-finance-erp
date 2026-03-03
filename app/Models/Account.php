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

    // 🔥 Recursive Total Balance
    public function getTotalBalanceAttribute()
    {
        if ($this->children->count() > 0) {
            return $this->children->sum(function ($child) {
                return $child->total_balance;
            });
        }

        return $this->balance;
    }

    // Credit limit tracking
    public function getUsedLimitAttribute()
    {
        if (!$this->track_limit) return 0;

        return $this->journalLines()
            ->sum(DB::raw('credit - debit'));
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
