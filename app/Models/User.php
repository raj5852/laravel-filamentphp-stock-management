<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\HistoryTypeEnum;
use App\UserTypeEnum;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'tenant_id',
        'is_admin',
        'type',
        'expires_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'type' => UserTypeEnum::class,
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            $loginUser = auth()->user();
            if ($loginUser->type == UserTypeEnum::USER) {
                $user->expires_at = $loginUser->expires_at;
            } else {
                $user->expires_at = now()->addMonths(intval($user->expires_at));
            }
        });

        static::created(function ($user) {
            $loginUser = auth()->user();

            if ($user->tenant_id == '') {
                if ($loginUser->type == UserTypeEnum::USER) {
                    $user->tenant_id = $loginUser->tenant_id;
                } else {
                    $user->tenant_id = $user->id;
                }

                $user->email_verified_at = now();
                $user->save();
            }
            if ($loginUser->type == UserTypeEnum::SUPERADMIN) {

                try {
                    DB::beginTransaction();

                    $role1 = Role::first();

                    $user->assignRole($role1);

                    DB::table('settings')->insert([
                        'company_name' => 'Your Company',
                        'email_address' => 'youremail@email.com',
                        'phone' => '1234567890',
                        'address' => 'Your Address',
                        'tenant_id' => $user->tenant_id,
                    ]);

                    DB::table('accounts')->insert([
                        'name' => 'Cash',
                        'opening_balance' => 0,
                        'current_balance' => 0,
                        'tenant_id' => $user->tenant_id,
                    ]);

                    $account = DB::table('accounts')->latest('id')->first();

                    DB::table('histories')->insert([
                        'account_id' => $account->id,
                        'date' => now(),
                        'amount' => $account->opening_balance ?: 0,
                        'type' => HistoryTypeEnum::OPENING_BALANCE->value,
                        'note' => '',
                        'tenant_id' => $user->tenant_id,
                        'created_at' => now(),
                    ]);

                    DB::table('units')->insert([
                        'unit_name' => 'PC',
                        'tenant_id' => $user->tenant_id,
                        'is_default' => 1,

                    ]);

                    DB::table('customers')->insert([
                        'customer_name' => 'Walk-in Customer',
                        'phone' => '000000',
                        'tenant_id' => $user->tenant_id,
                        'is_default' => 1,
                    ]);

                    DB::table('suppliers')->insert([
                        'supplier_name' => 'Default Supplier',
                        'email' => 'supplier@supplier.com',
                        'phone' => '0000000000',
                        'address' => 'Default Address',
                        'tenant_id' => $user->tenant_id,
                        'is_default' => 1,
                    ]);

                    DB::commit();
                } catch (\Throwable $th) {
                    DB::rollBack();
                }
            }
        });
    }
}
