<?php

namespace MGGFLOW\FlowShop;

use MGGFLOW\FlowShop\Entities\Product;
use MGGFLOW\FlowShop\Entities\Purchase;
use MGGFLOW\FlowShop\Exceptions\FailedToFindOrderPurchases;
use MGGFLOW\FlowShop\Exceptions\FailedToUpdateOrder;
use MGGFLOW\FlowShop\Exceptions\FailedToUpdatePurchases;
use MGGFLOW\FlowShop\Exceptions\InvalidPurchase;
use MGGFLOW\FlowShop\Interfaces\OrderData;
use MGGFLOW\FlowShop\Interfaces\ProductData;
use MGGFLOW\FlowShop\Interfaces\PurchaseData;

class EditOrderPurchase
{
    protected PurchaseData $purchaseData;
    protected ProductData $productData;
    protected OrderData $orderData;
    protected object $editedPurchase;

    protected int $orderId;
    protected ?array $purchases;
    protected array $orderPurchasesIds;
    protected array $products;
    protected object $editedPurchaseProduct;
    protected object $prevPurchase;
    protected array $calculatedPurchases;
    protected float $orderPrice;
    protected array $purchasesUpdate;
    protected ?array $purchasesUpdateResult;
    protected ?int $orderUpdateResult;

    public function __construct(
        PurchaseData $purchaseData, ProductData $productData, OrderData $orderData,
        object $editedPurchase
    )
    {
        $this->purchaseData = $purchaseData;
        $this->productData = $productData;
        $this->orderData = $orderData;

        $this->editedPurchase = $editedPurchase;
    }

    public function edit(): array
    {
        $this->parseOrderId();
        $this->loadOrderPurchases();
        $this->createOrderPurchasesIds();
        $this->checkOrderContentsPurchase();
        $this->loadPurchasesProducts();
        $this->setEditedPurchaseProduct();
        $this->setPrevPurchase();
        $this->validateEditedPurchase();
        $this->updateLocalPurchase();
        $this->provideAmounts();
        $this->calcOrderPrices();
        $this->createPurchasesUpdate();
        $this->updatePurchases();
        $this->checkPurchasesUpdate();
        $this->updateOrderPrice();
        $this->checkOrderUpdate();

        return $this->createResult();
    }

    protected function parseOrderId(){
        $this->orderId = $this->editedPurchase->order_id;
    }

    protected function loadOrderPurchases()
    {
        $this->purchases = $this->purchaseData->findForOrder($this->orderId);
        if (is_null($this->purchases)) {
            throw new FailedToFindOrderPurchases();
        }
        $this->purchases = array_column($this->purchases, null, 'id');
    }

    protected function createOrderPurchasesIds()
    {
        $this->orderPurchasesIds = array_column($this->purchases, 'id');
    }

    protected function checkOrderContentsPurchase()
    {
        if (!in_array($this->editedPurchase->id, $this->orderPurchasesIds)) {
            throw new InvalidPurchase();
        }
    }

    protected function loadPurchasesProducts()
    {
        $loader = new LoadPurchasesProducts($this->productData, $this->purchases);
        $this->products = $loader->load();
    }

    protected function setEditedPurchaseProduct(){
        $this->editedPurchaseProduct = $this->products[$this->editedPurchase->product_id];
    }

    protected function setPrevPurchase(){
        $this->prevPurchase = $this->purchases[$this->editedPurchase->id];
    }

    protected function validateEditedPurchase()
    {
        if ($this->prevPurchase->order_id != $this->editedPurchase->orderId){
            throw new InvalidPurchase();
        }
        if ($this->prevPurchase->product_id != $this->editedPurchase->product_id){
            throw new InvalidPurchase();
        }
        Purchase::validate($this->editedPurchase, $this->editedPurchaseProduct);
    }

    protected function updateLocalPurchase(){
        foreach ($this->editedPurchase as $property=>$value) {
            $this->purchases[$this->editedPurchase->id]->$property = $value;
        }
    }

    protected function provideAmounts(){
        if(!isset($this->editedPurchase->amount)
            or !Product::isAmountable($this->editedPurchaseProduct)
            or $this->editedPurchase->amount == $this->prevPurchase->amount
        ) return;

        $operatorPrev = new OperatePurchasesAmounts([$this->prevPurchase], $this->productData, $this->products);
        $operatorPrev->subAmounts();
        $operatorCurrent = new OperatePurchasesAmounts(
            [$this->purchases[$this->editedPurchase->id]],
            $this->productData
        );
        $amountResult = $operatorCurrent->addAmounts();
        $actualPurchase = array_pop($amountResult['purchases']);
        $this->purchases[$this->editedPurchase->id]->amount = $actualPurchase->amount;
    }

    protected function calcOrderPrices(){
        $calculator = new CalcOrderPrice($this->purchases, $this->products);
        $calculationResult = $calculator->calc();

        $this->calculatedPurchases = $calculationResult['purchases'];
        $this->orderPrice = $calculationResult['orderPrice'];
    }

    protected function createPurchasesUpdate(){
        foreach ($this->calculatedPurchases as $calculatedPurchase){
            $prevPurchase = $this->purchases[$calculatedPurchase->id];

            if ($prevPurchase->id == $this->editedPurchase->id
                or $prevPurchase->price != $calculatedPurchase->price
            ){
                $this->purchasesUpdate[] = $calculatedPurchase;
            }
        }
    }

    protected function updatePurchases(){
        $this->purchasesUpdateResult = $this->purchaseData->updateWithIds($this->purchasesUpdate);
    }

    protected function checkPurchasesUpdate(){
        if (empty($this->purchasesUpdateResult)){
            throw new FailedToUpdatePurchases();
        }
    }

    protected function updateOrderPrice() {
        $this->orderUpdateResult = $this->orderData->updateById($this->orderId, ['price' => $this->orderPrice]);
    }

    protected function checkOrderUpdate(){
        if(empty($this->orderUpdateResult)){
            throw new FailedToUpdateOrder();
        }
    }

    protected function createResult(): array {
        return [
            'purchases' => $this->calculatedPurchases,
            'orderPrice' => $this->orderPrice,
            'purchasesUpdate' => $this->purchasesUpdateResult,
            'orderUpdate' => $this->orderUpdateResult
        ];
    }
}