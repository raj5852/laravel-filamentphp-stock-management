<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

#[ScopedBy(TenantScope::class)]
class Brand extends Model
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

            if ($model->isDirty('brand_logo')) {
                $originalImage = $model->getOriginal('brand_logo');

                if ($originalImage && Storage::disk('public')->exists($originalImage)) {
                    Storage::disk('public')->delete($originalImage);
                }
            }

        });

        static::deleting(function ($brand) {
            if (Storage::disk('public')->exists($brand->brand_logo ?? '')) {
                Storage::disk('public')->delete($brand->brand_logo ?? '');
            }
        });
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
