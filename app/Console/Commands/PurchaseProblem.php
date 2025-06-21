<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PurchaseProblem extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:purchase-problem';

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
        $this->purchaseItemsQty();
        $allOrderItems = DB::table('order_items')->get();

        foreach ($allOrderItems as $orderItem) {
            $product_id = $orderItem->product_id;
            $qty = $orderItem->total_qty;
            $data = $this->addPurchaseExpense($product_id, $qty);
            DB::table('order_items')->where('id', $orderItem->id)->update([
                'purchase_ids' => json_encode($data),
            ]);
        }

        $this->setOrder();
    }

    public function setOrder()
    {
        $orders = DB::table('orders')->get();

        foreach ($orders as $order) {

            $order_items = DB::table('order_items')->where('order_id', $order->id)->get();
            $sum = 0;
            foreach ($order_items as $item) {
                $sum += collect(json_decode($item->purchase_ids))->sum('purchase_value');
            }

            DB::table('orders')->where('id', $order->id)->update([
                'profit' => DB::raw('receivable - '.$sum),
            ]);
        }
    }

    public function purchaseItemsQty()
    {
        DB::table('purchase_items')
            ->update([
                'available_qty' => DB::raw('total_qty'),
                'available_purchase_value' => DB::raw('total_rate'),
            ]);
    }

    public function addPurchaseExpense($productId, $qty)
    {

        $purchaseItems = DB::table('purchase_items')
            ->where('product_id', $productId)
            ->where('available_qty', '>', 0)
            ->get();

        $openingAndPurchaseQty = $qty;
        $initialTotalQty = 0;
        $indexByValue = [];

        foreach ($purchaseItems as $item) {

            $initialTotalQty += $item->available_qty;

            if ($initialTotalQty > $qty) {
                $available_qty = $item->available_qty - ($initialTotalQty - $qty);
            } else {
                $available_qty = $item->available_qty;
            }

            $indexByValue[] = [
                'qty' => $available_qty,
                'purchase_item_id' => $item->id,
                'purchase_id' => $item->purchase_id,
                'qty_in_text' => $this->getTotalStockInText($item->product_id, $available_qty),
                'purchase_value' => $this->singleUnitPurchasePrice($item->product_id, $item->rate ?: 0) * $available_qty,
            ];

            if ($initialTotalQty >= $openingAndPurchaseQty) {
                break;
            }
        }

        foreach ($indexByValue as $key => $value) {
            DB::table('purchase_items')->where('id', $value['purchase_item_id'])->decrement('available_qty', $value['qty']);
            $purchaseItem = DB::table('purchase_items')->find($value['purchase_item_id']);

            DB::table('purchase_items')->where('id', $value['purchase_item_id'])->update([
                'available_purchase_value' => $this->singleUnitPurchasePrice($purchaseItem->product_id, $purchaseItem->rate ?: 0) * $purchaseItem->available_qty,
            ]);
        }

        $data = collect($indexByValue)->toArray();
        // dd($indexByValue);

        return $data;
    }

    public function getTotalStockInText($productId, $totalStockAmount)
    {
        $product = DB::table('products')->find($productId);
        $mainUnitId = $product->unit_id;
        $mainUnit = DB::table('units')->find($mainUnitId);

        $relatedToUnit = $mainUnit->related_to_unit;
        if ($relatedToUnit == '') {
            return ($totalStockAmount ?: 0).' '.$mainUnit->unit_name;
        } else {
            $subUnit = DB::table('units')->find($relatedToUnit);

            $relatedByValue = ($mainUnit->related_by_value ?: 0);

            $getMainStock = (int) (($totalStockAmount ?: 0) / $relatedByValue);
            $getSubStock = ($totalStockAmount ?: 0) - ($relatedByValue * $getMainStock);

            if ($product['sub_unit'] == null) {
                return $getMainStock.' '.$mainUnit->unit_name;
            }

            return $getMainStock.' '.$mainUnit->unit_name.'  '.$getSubStock.' '.$subUnit->unit_name;
        }
    }

    public function singleUnitPurchasePrice($productId, $purchase_cost = null)
    {
        $product = DB::table('products')->find($productId);
        $purchasePrice = $purchase_cost == null ? $product->purchase_cost : $purchase_cost;

        if ($product->sub_unit == '') {
            return $purchasePrice;
        } else {
            $unit = DB::table('units')->find($product->unit_id);
            $singleUnitPurchasePrice = $purchasePrice / $unit->related_by_value;

            return $singleUnitPurchasePrice;
        }
    }
}
