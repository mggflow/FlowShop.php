<?php

namespace MGGFLOW\FlowShop\Entities;

use MGGFLOW\FlowShop\Exceptions\InvalidPurchase;

class Purchase
{
    public static function validate(object $purchase, object $product){
        if ($product->id != $purchase->product_id){
            throw new InvalidPurchase();
        }

        if ($product->amount === null and $purchase->amount != null) {
            throw new InvalidPurchase();
        }

        if ($product->durations != null and in_array($purchase->duration, explode(',', $product->durations))) {
            throw new InvalidPurchase();
        }
    }
}