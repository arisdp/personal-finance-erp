<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasAuditTrail;

class JournalEntry extends Model
{
    use HasUuids, SoftDeletes, HasAuditTrail;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'workspace_id',
        'date',
        'reference',
        'description',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }

    public function lines()
    {
        return $this->hasMany(JournalLine::class);
    }

    // Total debit dari semua lines
    public function getTotalDebitAttribute()
    {
        return $this->lines->sum('debit');
    }

    // Total credit dari semua lines
    public function getTotalCreditAttribute()
    {
        return $this->lines->sum('credit');
    }

    // Cek apakah journal balanced
    public function getIsBalancedAttribute()
    {
        return abs($this->total_debit - $this->total_credit) < 0.01;
    }
}
