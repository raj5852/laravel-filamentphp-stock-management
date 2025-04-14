<?php

namespace App\Models;

use App\HistoryTypeEnum;
use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;

#[ScopedBy(TenantScope::class)]
class History extends Model
{
    //

    protected $guarded = [];

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

    public $casts = [
        'type' => HistoryTypeEnum::class,
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }
}
