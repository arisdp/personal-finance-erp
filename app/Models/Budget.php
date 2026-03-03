<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class Budget extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'id',
        'workspace_id',
        'account_id',
        'amount',
        'period_type',
        'year',
        'month',
        'created_by'
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }
}
