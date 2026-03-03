<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;

trait HasAuditTrail
{
    public static function bootHasAuditTrail(): void
    {
        static::creating(function (Model $model) {
            if (auth()->check() && $model->isFillable('created_by')) {
                $model->created_by = $model->created_by ?? auth()->id();
            }
        });

        static::updating(function (Model $model) {
            if (auth()->check() && $model->isFillable('updated_by')) {
                $model->updated_by = auth()->id();
            }
        });

        static::deleting(function (Model $model) {
            if (auth()->check() && $model->isFillable('deleted_by')) {
                $model->deleted_by = auth()->id();
                $model->saveQuietly();
            }
        });
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    public function deleter()
    {
        return $this->belongsTo(\App\Models\User::class, 'deleted_by');
    }
}
