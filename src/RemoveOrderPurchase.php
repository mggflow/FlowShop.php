<?php

namespace MGGFLOW\FlowShop;

use MGGFLOW\FlowShop\Exceptions\FailedToFindOrderPurchases;
use MGGFLOW\FlowShop\Exceptions\FailedToRemovePurchase;
use MGGFLOW\FlowShop\Exceptions\FailedToUpdateOrder;
use MGGFLOW\FlowShop\Exceptions\FailedToUpdatePurchases;
use MGGFLOW\FlowShop\Exceptions\InvalidPurchase;
use MGGFLOW\FlowShop\Interfaces\OrderData;
use MGGFLOW\FlowShop\Interfaces\ProductData;
use MGGFLOW\FlowShop\Interfaces\PurchaseData;

class RemoveOrderPurchase
{
    protected PurchaseData $purchaseData;
    protected ProductData $productData;
    protected OrderData $orderData;
    protected int $orderId;
    protected int $purchaseId;

    protected ?array $purchases;
    protected array $remainingPurchases;
    protected ?array $products;
    protected array $calculatedPurchases;
    protected float $orderPrice;
    protected bool $purchaseRemovingResult;
    protected array $productAmountRestoringResult;
    protected array $purchasesUpdate;
    protected ?array $purchasesUpdateResult;
    protected ?int $orderUpdateResult;

    public function __construct(PurchaseData $purchaseData, ProductData $productData, OrderData $orderData,
                                int          $orderId, int $purchaseId
    )
    {
        $this->purchaseData = $purchaseData;
        $this->productData = $productData;
        $this->orderData = $orderData;
        $this->orderId = $orderId;
        $this->purchaseId = $purchaseId;
    }

    public function remove()
    {
        $this->loadOrderPurchases();
        $this->checkOrderContentsPurchase();
        $this->createRemainingPurchases();
        $this->loadPurchasesProducts();
        $this->calcOrderPrices();
        $this->removePurchase();
        $this->checkPurchaseRemoving();
        $this->restoreProductAmount();
        $this->createPurchasesUpdate();
        $this->updatePurchases();
        $this->checkPurchasesUpdate();
        $this->updateOrderPrice();
        $this->checkOrderUpdate();

        return $this->createResult();
    }

    protected function loadOrderPurchases()
    {
        $this->purchases = $this->purchaseData->findForOrder($this->orderId);
        if (is_null($this->purchases)) {
            throw new FailedToFindOrderPurchases();
        }
        $this->purchases = array_column($this->purchases, null, 'id');
    }

    protected function checkOrderContentsPurchase()
    {
        if (empty($this->purchases[$this->purchaseId])) {
            throw new InvalidPurchase();
        }
    }

    protected function createRemainingPurchases() {
        $this->remainingPurchases = [];
        foreach ($this->purchases as $purchase){
            if ($purchase->id == $this->purchaseId) continue;

            $this->remainingPurchases[$purchase->id] = $purchase;
        }
    }

    protected function loadPurchasesProducts()
    {
        $loader = new LoadPurchasesProducts($this->productData, $this->purchases);
        $this->products = $loader->load();
    }

    protected function calcOrderPrices(){
        $calculator = new CalcOrderPrice($this->remainingPurchases, $this->products);
        $calculationResult = $calculator->calc();

        $this->calculatedPurchases = $calculationResult['purchases'];
        $this->orderPrice = $calculationResult['orderPrice'];
    }

    protected function removePurchase(){
        $this->purchaseRemovingResult = $this->purchaseData->deleteById($this->purchaseId);
    }

    protected function checkPurchaseRemoving(){
        if(!$this->purchaseRemovingResult){
            throw new FailedToRemovePurchase();
        }
    }

    protected function restoreProductAmount(){
        $purchase = $this->purchases[$this->purchaseId];
        $operator = new OperatePurchasesAmounts([$purchase],$this->productData,$this->products);
        $this->productAmountRestoringResult = $operator->subAmounts();
    }

    protected function createPurchasesUpdate(){
        foreach ($this->calculatedPurchases as $calculatedPurchase){
            $prevPurchase = $this->remainingPurchases[$calculatedPurchase->id];

            if ($prevPurchase->price != $calculatedPurchase->price
            ){
                $this->purchasesUpdate[] = $calculatedPurchase;
            }
        }
    }

    protected function updatePurchases(){
        if(empty($this->purchasesUpdate)) return;
        $this->purchasesUpdateResult = $this->purchaseData->updateWithIds($this->purchasesUpdate);
    }

    protected function checkPurchasesUpdate(){
        if (empty($this->purchasesUpdateResult) and !empty($this->purchasesUpdate)){
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
            'productAmountRestoring' => $this->productAmountRestoringResult,
            'purchasesUpdate' => $this->purchasesUpdateResult,
            'orderUpdate' => $this->orderUpdateResult
        ];
    }
}