<?php

use App\Models\Customer;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Unit;
use Illuminate\Support\Number;

if (! function_exists('getTotalStock')) {
    function getTotalStock($productId, $openingStockValue = null, $subOpeningStockValue = null)
    {
        $mainUnitId = Product::find($productId)->unit_id;
        $mainUnit = Unit::find($mainUnitId);

        $relatedToUnit = $mainUnit->related_to_unit;
        if ($relatedToUnit != '') {
            $relatedByValue = $mainUnit->related_by_value;
        } else {
            $relatedByValue = 1;
        }

        $totalMainUnit = ($openingStockValue ?: 0) * $relatedByValue;

        return $totalMainUnit + ($subOpeningStockValue ?: 0);
    }
}

if (! function_exists('getTotalStockInText')) {
    function getTotalStockInText($productId, $totalStockAmount)
    {
        $product = Product::find($productId);
        $mainUnitId = $product->unit_id;
        $mainUnit = Unit::find($mainUnitId);

        $relatedToUnit = $mainUnit->related_to_unit;
        if ($relatedToUnit == '') {
            return ($totalStockAmount ?: 0).' '.$mainUnit->unit_name;
        } else {
            $subUnit = Unit::find($relatedToUnit);

            $relatedByValue = ($mainUnit->related_by_value ?: 0);

            $getMainStock = (int) (($totalStockAmount ?: 0) / $relatedByValue);
            $getSubStock = ($totalStockAmount ?: 0) - ($relatedByValue * $getMainStock);

            if ($product['sub_unit'] == null) {
                return $getMainStock.' '.$mainUnit->unit_name;
            }

            return $getMainStock.' '.$mainUnit->unit_name.'  '.$getSubStock.' '.$subUnit->unit_name;
        }
    }
}

if (! function_exists('getTotalStockInTextWithoutModal')) {
    function getTotalStockInTextWithoutModal($productUnitSubUnit, $totalStockAmount)
    {
        $product = $productUnitSubUnit;
        $mainUnit = $product->unit;

        $relatedToUnit = $mainUnit->related_to_unit;
        if ($relatedToUnit == '') {
            return ($totalStockAmount ?: 0).' '.$mainUnit->unit_name;
        } else {
            $subUnit = $product->subunit;

            $relatedByValue = ($mainUnit->related_by_value ?: 0);

            $getMainStock = (int) (($totalStockAmount ?: 0) / $relatedByValue);
            $getSubStock = ($totalStockAmount ?: 0) - ($relatedByValue * $getMainStock);

            if ($product['sub_unit'] == null) {
                return $getMainStock.' '.$mainUnit->unit_name;
            }

            return $getMainStock.' '.$mainUnit->unit_name.'  '.$getSubStock.' '.$subUnit->unit_name;
        }
    }
}

if (! function_exists('singleUnitSalePrice')) {

    function singleUnitSalePrice($productId)
    {
        $product = Product::find($productId);
        $salePrice = $product->sale_price;

        if ($product->sub_unit == '') {
            return $salePrice;
        } else {
            $unit = Unit::find($product->unit_id);
            $singleUnitSalePrice = $salePrice / $unit->related_by_value;

            return $singleUnitSalePrice;
        }
    }
}

if (! function_exists('singleUnitPurchasePrice')) {

    function singleUnitPurchasePrice($productId, $purchase_cost = null)
    {
        $product = Product::find($productId);
        $purchasePrice = $purchase_cost == null ? $product->purchase_cost : $purchase_cost;

        if ($product->sub_unit == '') {
            return $purchasePrice;
        } else {
            $unit = Unit::find($product->unit_id);
            $singleUnitPurchasePrice = $purchasePrice / $unit->related_by_value;

            return $singleUnitPurchasePrice;
        }
    }
}

if (! function_exists('supplierDue')) {

    function supplierDue($id)
    {
        $supplier = Supplier::query()->withSum('purchases', 'due')->find($id);

        return $supplier->purchases_sum_due ?? 0;
    }
}

if (! function_exists('customerDue')) {

    function customerDue($id)
    {
        $customer = Customer::query()->withSum('orders', 'due')->find($id);

        return $customer->orders_sum_due ?? 0;
    }
}

if (! function_exists('numberToBanglaWord')) {

    function numberToBanglaWord($num = 0)
    {
        return Number::spell($num);
    }
}
