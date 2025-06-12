<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ReceivableToTotalNoDiscount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:receivable-to-total-no-discount';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $settings = DB::table('settings')->get();
        foreach ($settings as $setting) {
            DB::table('settings')
                ->where('id', $setting->id)
                ->update([
                    'order_sms' => 'Dear Customer,

Order #{bill_no} for Tk {amount} has been completed.
Thanks for your order.

{company_name}',
                ]);
        }

        $permissions = [
            'promotional_sms',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $role = Role::firstOrCreate(['name' => 'main_user', 'tenant_id' => 0]);

        // Assign all permissions to the role
        $role->syncPermissions(Permission::all());
    }
}
