<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

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
        $orders =  DB::table('orders')->get();
        foreach ($orders as $order) {
            DB::table('orders')
                ->where('id', $order->id)
                ->update([
                    'total_no_discount' => $order->receivable
                ]);
        }
    }
}
