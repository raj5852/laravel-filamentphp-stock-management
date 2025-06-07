<?php

namespace App\Models;

use App\HistoryTypeEnum;
use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;

#[ScopedBy(TenantScope::class)]
class Expense extends Model
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

            $account = Account::find($model->account_id);
            $account->decrement('current_balance', $model->amount);
            $account->histories()->create([
                'date' => today(),
                'amount' => $model->amount,
                'type' => HistoryTypeEnum::SPENT_OR_WITHDRAW->value,
                'note' => '',
                'expense_id' => $model->id,
            ]);
        });

        static::deleting(function ($model) {
            $account = Account::find($model->account_id);
            $account->increment('current_balance', $model->amount);
            $model->histories()->delete();
        });

        static::updating(function ($model) {
            $user = auth()->user();
            $model->updated_by = $user->id;
        });
    }

    public function expenseCategory()
    {
        return $this->belongsTo(ExpenseCategory::class);
    }

    public function histories()
    {
        return $this->hasMany(History::class);
    }
}
