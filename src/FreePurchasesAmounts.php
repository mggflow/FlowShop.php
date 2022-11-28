<?php

namespace MGGFLOW\FlowShop;

use MGGFLOW\FlowShop\Exceptions\FailedToFindProducts;
use MGGFLOW\FlowShop\Interfaces\ProductData;

class FreePurchasesAmounts
{
    protected array $purchases;
    protected ProductData $productData;

    protected array $productsIds;
    protected ?array $products;

    protected array $productsAmounts;

    public function __construct(array $purchases, ProductData $productData)
    {
        $this->purchases = $purchases;
        $this->productData = $productData;
    }

    public function free(){
        $this->loadPurchasesProducts();
        $this->createProductsAmounts();

        return $this->editProductsAmounts();
    }

    protected function loadPurchasesProducts() {
        $this->productsIds = array_column($this->purchases, 'product_id');
        $this->products = $this->productData->findByIds($this->productsIds);
        if (empty($this->products)) {
            throw new FailedToFindProducts();
        }
        $this->products = array_column($this->products, null, 'id');
    }

    protected function createProductsAmounts(){
        $this->productsAmounts = [];
        foreach ($this->purchases as $purchase) {
            if ($purchase->amount == 0) continue;
            $product = $this->products[$purchase->product_id];
            $this->productsAmounts[$product->id] = $product->amount + $purchase->amount;
        }
    }

    protected function editProductsAmounts(): ?int
    {
        if (empty($this->productsAmounts)) return 0;

        return $this->productData->updateProductsAmounts($this->productsAmounts);
    }
}