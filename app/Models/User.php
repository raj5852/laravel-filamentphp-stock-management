<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\HistoryTypeEnum;
use App\UserTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

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
            $user->expires_at = now()->addMonths(intval($user->expires_at));
        });

        static::created(function ($user) {
            if ($user->tenant_id == '') {
                $user->tenant_id = $user->id;
                $user->email_verified_at = now();
                $user->save();
            }
            try {
                DB::beginTransaction();

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

                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();

            }

        });
    }
}
