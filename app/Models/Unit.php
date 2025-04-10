<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;

#[ScopedBy(TenantScope::class)]
class Unit extends Model
{
    protected $guarded = [];

    public function relatedTo()
    {
        return $this->belongsTo(Unit::class, 'related_to_unit');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $user = auth()->user();
            $model->tenant_id = $user->tenant_id;
            $model->created_by = $user->id;
        });

        static::updating(function ($model) {
            $user = auth()->user();
            $model->updated_by = $user->id;
        });
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
