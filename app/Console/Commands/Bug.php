<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Scopes\TenantScope;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class Bug extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:bug';

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
       
      // orderitems 
        $products = Product::query()->where('unit_id','!=',null)->where('sub_unit',null)
            ->withoutGlobalScope(TenantScope::class)
            ->whereHas('unit',function($query){
                $query->withoutGlobalScope(TenantScope::class)
                ->where('related_by_value','!=',null);
            })
            ->with('orderitems',function($query){
                $query->withoutGlobalScope(TenantScope::class)
                ->with('order',function($query){
                    $query->withoutGlobalScope(TenantScope::class);
                })
                ;
            })
            ->get();



   

    foreach($products ?? [] as $item){
        
        foreach($item->orderitems as $orderItem){
            $ids = $orderItem->purchase_ids;
            foreach ($ids as &$purchase) {
                $purchase_item_id = DB::table('purchase_items')->where('id', $purchase['purchase_item_id'])->first();
               
                $purchase['purchase_value'] = $purchase_item_id->rate;
            }
            $newPurchaseCost = array_sum(array_column($ids, 'purchase_value'));
            OrderItem::where('id',$orderItem->id)->withoutGlobalScope(TenantScope::class)->update([
                'purchase_ids'=>$ids,
                'purchase_cost'=>$newPurchaseCost
            ]);

        }
        
    }

   
    // order

    foreach($products as $product){
        foreach($product->orderitems as $orderItem){
            $order = $orderItem->order;
            $receable = $order->receivable;

            $orderAndOrderItem = Order::query()->withoutGlobalScope(TenantScope::class)->with('orderitems',function($query){
                $query->withoutGlobalScope(TenantScope::class);
            }) ->find($order->id);

            Order::query()->withoutGlobalScope(TenantScope::class)->where('id',$order->id)
                ->update([
                    'profit' => ($receable ?: 0) - ($orderAndOrderItem->orderitems->sum('purchase_cost') ?: 0),
                ]);
        
        }
    }

    
    }
}
