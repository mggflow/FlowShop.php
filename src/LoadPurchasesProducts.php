<?php

namespace MGGFLOW\FlowShop;

use MGGFLOW\FlowShop\Exceptions\FailedToFindProducts;
use MGGFLOW\FlowShop\Interfaces\ProductData;

class LoadPurchasesProducts
{
    protected ProductData $productData;
    protected array $purchases;

    protected array $productsIds;
    protected ?array $products;

    public function __construct(ProductData $productData, array &$purchases)
    {
        $this->productData = $productData;
        $this->purchases = $purchases;
    }

    public function load(): ?array
    {
        $this->takeProductsIds();
        $this->findProducts();
        $this->checkProductsSearch();
        $this->indexById();

        return $this->getProducts();
    }

    protected function takeProductsIds(){
        $this->productsIds = array_column($this->purchases, 'product_id');
    }

    protected function findProducts(){
        $this->products = $this->productData->findByIds($this->productsIds);
    }

    protected function checkProductsSearch(){
        if (empty($this->products)) {
            throw new FailedToFindProducts();
        }
    }

    protected function indexById(){
        $this->products = array_column($this->products, null, 'id');
    }

    protected function getProducts(): ?array
    {
        return $this->products;
    }
}