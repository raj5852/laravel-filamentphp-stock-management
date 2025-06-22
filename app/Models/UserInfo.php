<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;

// #[ScopedBy(TenantScope::class)]
class UserInfo extends Model
{
    //

    protected $guarded = [];


    function user()
    {
        return $this->belongsTo(User::class);
    }
}
