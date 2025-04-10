<?php

namespace App\Models;

use App\HistoryTypeEnum;
use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;

#[ScopedBy(TenantScope::class)]
class Account extends Model
{
    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $user = auth()->user();
            $model->tenant_id = $user->tenant_id;
            $model->created_by = $user->id;
        });

        static::created(function ($model) {

            $model->increment('current_balance', $model->opening_balance);
            $model->histories()->create([
                'date' => now(),
                // 'owner_id' => '',
                'amount' => $model->opening_balance,
                'type' => HistoryTypeEnum::OPENING_BALANCE->value,
                'note' => '',
            ]);

        });

        static::updating(function ($model) {
            $user = auth()->user();
            $model->updated_by = $user->id;
        });
    }

    public function histories()
    {
        return $this->hasMany(History::class);
    }
}
