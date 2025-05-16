<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

#[ScopedBy(TenantScope::class)]
class Supplier extends Model
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
            if ($model->opening_receivable == '') {
                $model->opening_receivable = 0;
            }
            if ($model->opening_payable == '') {
                $model->opening_payable = 0;
            }

            $model->save();
            $model->wallet = $model->opening_receivable - $model->opening_payable;

            $model->save();

            $amount = $model->opening_receivable - $model->opening_payable;

            if ($amount != 0) {

                if ($amount < 0) {
                    $amount = $amount;
                    $particulars = 'Opening Payable';
                } else {
                    $amount = $amount;
                    $particulars = 'Opening Receivable';
                }

                OpeningBalance::create([
                    'supplier_id' => $model->id,
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

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    protected function totaldue(): Attribute
    {
        return Attribute::make(
            get: fn () => 0,
        );
    }


    function histories(){
        return $this->hasMany(History::class);
    }
}
