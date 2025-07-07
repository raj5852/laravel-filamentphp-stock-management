<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Spatie\Permission\Models\Role as ModelsRole;

#[ScopedBy(TenantScope::class)]
class Role extends ModelsRole
{
    //
    protected $table = 'roles';

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
            // $user = auth()->user();
            // $model->updated_by = $user->id;
        });
    }
}
