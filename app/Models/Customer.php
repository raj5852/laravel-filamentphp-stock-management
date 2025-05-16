<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

#[ScopedBy(TenantScope::class)]
class Customer extends Model
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

        static::created(function ($model) {
            if ($model->opening_receivable == '') {
                $model->opening_receivable = 0;
            }
            if ($model->opening_payable == '') {
                $model->opening_payable = 0;
            }

            $model->save();
            $model->wallet = $model->opening_payable - $model->opening_receivable;

            $model->save();

            $amount = $model->opening_payable - $model->opening_receivable;

            if ($amount != 0) {

                if ($amount < 0) {
                    $amount = abs($amount);
                    $particulars = 'Opening Receivable';
                } else {
                    $amount = -($amount);
                    $particulars = 'Opening Payable';
                }

                OpeningBalance::create([
                    'customer_id' => $model->id,
                    'amount' => $amount,
                    'particulars' => $particulars,
                ]);
            }

        });

        static::updating(function ($model) {
            $user = auth()->user();
            $model->updated_by = $user->id;
        });
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    protected function totalDue(): Attribute
    {
        return Attribute::make(
            get: fn () => 0,
        );
    }

    function histories(){
        return $this->hasMany(History::class);
    }
}
