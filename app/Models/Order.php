<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ScopedBy(TenantScope::class)]
class Order extends Model
{
    // use HasFactory;
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

    public function orderitems()
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function histories()
    {
        return $this->hasMany(History::class);
    }
}
