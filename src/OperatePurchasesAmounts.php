<?php

namespace MGGFLOW\FlowShop;

use MGGFLOW\FlowShop\Exceptions\IncorrectPurchasesProducts;
use MGGFLOW\FlowShop\Interfaces\ProductData;

class OperatePurchasesAmounts
{
    protected array $purchases;
    protected ProductData $productData;

    protected ?array $products;

    protected bool $addition;
    protected object $purchase;
    protected object $product;
    protected int $productAmount;
    protected array $productsAmounts;

    protected ?int $productsEdited;

    public function __construct(array $purchases, ProductData $productData, ?array $products = null)
    {
        $this->purchases = $purchases;
        $this->productData = $productData;
        $this->products = $products;
    }

    public function addAmounts(): array
    {
        $this->setAdditionFlag();
        $this->loadPurchasesProducts();
        $this->createProductsAmounts();

        $this->editProductsAmounts();

        return $this->createResult();
    }

    public function subAmounts(): array
    {
        $this->setSubtractionFlag();
        $this->loadPurchasesProducts();
        $this->createProductsAmounts();

        $this->editProductsAmounts();

        return $this->createResult();
    }

    protected function setAdditionFlag() {
        $this->addition = true;
    }

    protected function setSubtractionFlag() {
        $this->addition = false;
    }

    protected function loadPurchasesProducts() {
        if (!empty($this->products)) return;

        $loader = new LoadPurchasesProducts($this->productData, $this->purchases);
        $this->products = $loader->load();
    }

    protected function createProductsAmounts(){
        $this->productsAmounts = [];
        foreach ($this->purchases as &$this->purchase) {
            $this->checkProductExistence();
            $this->setProduct();
            $this->setAmounts();
        }
    }

    protected function checkProductExistence(){
        if (!isset($this->products[$this->purchase->product_id])){
            throw new IncorrectPurchasesProducts();
        }
    }

    protected function setProduct(){
        $this->product = $this->products[$this->purchase->product_id];
    }

    protected function setAmounts()
    {
        if (empty($this->purchase->amount)) return;

        if ($this->addition){
            $this->productAmount = $this->product->amount + $this->purchase->amount;
        }else{
            if ($this->purchase->amount > $this->product->amount) {
                $this->purchase->amount = $this->product->amount;
            }

            $this->productAmount = $this->product->amount - $this->purchase->amount;
        }

        if ($this->product->amount != $this->productAmount) {
            $this->productsAmounts[$this->product->id] = $this->productAmount;
        }
    }

    protected function editProductsAmounts()
    {
        if (empty($this->productsAmounts)) $this->productsEdited = 0;

        $this->productsEdited = $this->productData->updateProductsAmounts($this->productsAmounts);
    }

    protected function createResult(): array
    {
        return [
            'purchases' => $this->purchases,
            'productsEdited' => $this->productsEdited,
        ];
    }
}