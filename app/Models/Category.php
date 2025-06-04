<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

#[ScopedBy(TenantScope::class)]
class Category extends Model
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

        static::updating(function ($model) {
            $user = auth()->user();
            $model->updated_by = $user->id;

            if ($model->isDirty('image')) {
                $originalImage = $model->getOriginal('image');

                if ($originalImage && Storage::disk('public')->exists($originalImage)) {
                    Storage::disk('public')->delete($originalImage);
                }
            }
        });

        static::deleting(function ($category) {
            if (Storage::disk('public')->exists($category->image ?? '')) {
                Storage::disk('public')->delete($category->image ?? '');
            }
        });
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function orderItems()
    {
        return $this->hasManyThrough(OrderItem::class, Product::class)->withoutGlobalScope(TenantScope::class);
    }

    public function purchaseItems()
    {
        return $this->hasManyThrough(PurchaseItem::class, Product::class)->withoutGlobalScope(TenantScope::class);
    }

    public function productDetails()
    {
        return $this->hasManyThrough(ProductDetail::class, Product::class)->withoutGlobalScope(TenantScope::class);
    }
}
