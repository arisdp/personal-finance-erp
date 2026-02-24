<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Account extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'code',
        'name',
        'type',
        'parent_id',
        'is_postable',
        'credit_limit',
        'track_limit'
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

    // 🔥 Recursive Total
    public function getTotalBalanceAttribute()
    {
        if ($this->children->count() > 0) {
            return $this->children->sum(function ($child) {
                return $child->total_balance;
            });
        }

        return $this->balance;
    }

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
}
