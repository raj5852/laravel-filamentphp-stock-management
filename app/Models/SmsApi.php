<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;

// #[ScopedBy(TenantScope::class)]
class SmsApi extends Model
{
    protected $guarded = [];
}
