<?php

namespace MGGFLOW\FlowShop;

use MGGFLOW\FlowShop\Interfaces\ProductData;

class FixatePurchases
{
    protected ProductData $productData;
    protected array $purchases;
    protected array $productsByIds;

    protected object $purchase;
    protected object $product;
    protected int $productAmount;

    protected array $newProductsAmounts;

    public function __construct(ProductData $productData, array $purchases, array $productsByIds)
    {
        $this->productData = $productData;
        $this->purchases = $purchases;
        $this->productsByIds = $productsByIds;
    }

    public function fixate(): array
    {
        $this->resetProductsAmounts();
        $this->handlePurchases();
        $this->editProductsAmounts();

        return $this->getPurchases();
    }

    protected function resetProductsAmounts()
    {
        $this->newProductsAmounts = [];
    }

    protected function handlePurchases()
    {
        foreach ($this->purchases as &$this->purchase) {
            $this->setProduct();
            $this->setAmounts();
        }
    }

    protected function setProduct()
    {
        $this->product = $this->productsByIds[$this->purchase->product_id];
    }

    protected function setAmounts()
    {
        if ($this->purchase->amount == null) return;

        if ($this->purchase->amount > $this->product->amount) {
            $this->purchase->amount = $this->product->amount;
        }

        $this->productAmount = $this->product->amount - $this->purchase->amount;

        if ($this->product->amount != $this->productAmount) {
            $this->newProductsAmounts[$this->product->id] = $this->productAmount;
        }
    }

    protected function editProductsAmounts()
    {
        if (empty($this->newProductsAmounts)) return;

        $this->productData->updateProductsAmounts($this->newProductsAmounts);
    }

    protected function getPurchases(): array
    {
        return $this->purchases;
    }
}