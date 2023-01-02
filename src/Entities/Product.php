<?php

namespace MGGFLOW\FlowShop\Entities;

class Product
{
    const DURATION_PERIOD = 60;

    public static function isAmountable(object $product): bool{
        return $product->amount !== null;
    }
}