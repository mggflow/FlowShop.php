<?php

namespace MGGFLOW\FlowShop;

use MGGFLOW\FlowShop\Entities\Product;

class CalcOrderPrice
{
    protected array $purchases;
    protected array $productsByIds;

    protected object $purchase;
    protected object $product;

    protected float $orderPrice;

    public function __construct(array $purchases, array $productsByIds)
    {
        $this->purchases = $purchases;
        $this->productsByIds = $productsByIds;
    }

    public function calc(): array
    {
        $this->initOrderPrice();

        foreach ($this->purchases as &$this->purchase){
            $this->setProduct();
            $this->calcPurchasePrice();
            $this->supplementOrderPrice();
        }

        return $this->getResult();
    }

    protected function initOrderPrice() {
        $this->orderPrice = 0.0;
    }

    protected function setProduct(){
        $this->product = $this->productsByIds[$this->purchase->product_id];
    }

    protected function calcPurchasePrice() {
        if ($this->purchase->duration != null){
            $this->purchase->price = ($this->purchase->duration / Product::DURATION_PERIOD) * $this->product->price;
        }else{
            $this->purchase->price = $this->product->price;
        }
    }

    protected function supplementOrderPrice() {
        if ($this->purchase->amount == null){
            $this->orderPrice += $this->purchase->price;
        }else{
            $this->orderPrice += $this->purchase->price * $this->purchase->amount;
        }
    }

    protected function getResult() {
        return [
            'purchases' => $this->purchases,
            'orderPrice' => $this->orderPrice
        ];
    }
}