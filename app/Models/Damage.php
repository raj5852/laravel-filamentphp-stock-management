<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;

#[ScopedBy(TenantScope::class)]
class Damage extends Model
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

        static::deleting(function ($damage) {
            $product = Product::find($damage->product_id);
            $totalStock = $product->productdetails->available_stock;
            $totalDamageStock = $product->productdetails->damaged;

            $product->productdetails()->increment('available_stock', $damage->total_qty);
            $product->productdetails()->decrement('damaged', $damage->total_qty);

            $product->productdetails()->update([
                'available_stock_in_text' => getTotalStockInText($product->id, ($totalStock + $damage->total_qty)),
                'damaged_in_text' => getTotalStockInText($product->id, ($totalDamageStock - $damage->total_qty)),
            ]);

        });

    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
