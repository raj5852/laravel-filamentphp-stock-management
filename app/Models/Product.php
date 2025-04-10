<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

#[ScopedBy(TenantScope::class)]
class Product extends Model
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
            if ($model->product_code == '') {
                $model->product_code = '000000'.$model->id;
            }
            $model->save();

            $qty = getTotalStock($model->id, $model->first_opening_stock, $model->second_opening_stock);
            $qty_in_text = getTotalStockInText($model->id, $qty);

            $empty_qty = 0;
            $empty_qty_in_text = getTotalStockInText($model->id, $empty_qty);

            $model->productdetails()->create([
                'single_unit_sale_price' => singleUnitSalePrice($model->id),
                'single_unit_purchase_price' => singleUnitPurchasePrice($model->id),

                'purchased' => $qty,
                'purchased_in_text' => $qty_in_text,

                'sold' => $empty_qty,
                'sold_in_text' => $empty_qty_in_text,

                'damaged' => $empty_qty,
                'damaged_in_text' => $empty_qty_in_text,

                'returned' => $empty_qty,
                'returned_in_text' => $empty_qty_in_text,

                'available_stock' => $qty,
                'available_stock_in_text' => $qty_in_text,
            ]);

        });

        static::updating(function ($model) {
            $user = auth()->user();
            $model->updated_by = $user->id;

            if ($model->isDirty('product_image')) {
                $originalImage = $model->getOriginal('product_image');

                if ($originalImage && Storage::disk('public')->exists($originalImage)) {
                    Storage::disk('public')->delete($originalImage);
                }
            }

        });

        static::updated(function ($model) {

            $model->productdetails()->update([
                'single_unit_sale_price' => singleUnitSalePrice($model->id),
                'single_unit_purchase_price' => singleUnitPurchasePrice($model->id),
            ]);
        });

        static::deleting(function ($product) {
            if (Storage::disk('public')->exists($product->product_image ?? '')) {
                Storage::disk('public')->delete($product->product_image ?? '');
            }
        });

    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function productdetails()
    {
        return $this->hasOne(ProductDetail::class, 'product_id')->withDefault([]);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function subunit()
    {
        return $this->belongsTo(Unit::class, 'sub_unit');
    }

    function damages()
    {
        return $this->hasMany(Damage::class);
    }
}
