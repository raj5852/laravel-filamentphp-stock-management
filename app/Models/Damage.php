<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use App\Services\ExpensePurchase;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

#[ScopedBy(TenantScope::class)]
class Damage extends Model
{
    //

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'purchase_ids' => 'array',
        ];
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

        static::deleting(function ($damage) {
            try {
                DB::beginTransaction();

                $product = Product::find($damage->product_id);
                $totalStock = $product->productdetails->available_stock;
                $totalDamageStock = $product->productdetails->damaged;

                $product->productdetails()->increment('available_stock', $damage->total_qty);
                $product->productdetails()->decrement('damaged', $damage->total_qty);

                $product->productdetails()->update([
                    'available_stock_in_text' => getTotalStockInText($product->id, ($totalStock + $damage->total_qty)),
                    'damaged_in_text' => getTotalStockInText($product->id, ($totalDamageStock - $damage->total_qty)),
                ]);

                // ExpensePurchase::deletePurchaseExpense($damage->purchase_ids);
                foreach ($damage->purchase_ids ?? [] as $purchase_id) {
                    $purchaseItem = PurchaseItem::find($purchase_id['purchase_item_id']);

                    $purchaseItem->increment('available_qty', $purchase_id['qty']);

                    $purchaseItem->update([
                        'available_purchase_value' => singleUnitPurchasePrice($purchaseItem->product_id, $purchaseItem->rate ?: 0) * $purchaseItem->available_qty,
                    ]);
                }
                if ($product->has_varient == 1) {
                    updateProductVarient($product->id, $damage->varient, available_stock: $damage->total_qty);
                }

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
                // Handle exception

            }
        });
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
