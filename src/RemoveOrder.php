<?php

namespace MGGFLOW\FlowShop;

use MGGFLOW\FlowShop\Exceptions\FailedToDeleteOrder;
use MGGFLOW\FlowShop\Exceptions\FailedToDeletePurchases;
use MGGFLOW\FlowShop\Interfaces\OrderData;
use MGGFLOW\FlowShop\Interfaces\PurchaseData;

class RemoveOrder
{
    protected OrderData $orderData;
    protected PurchaseData $purchaseData;
    protected int $orderId;

    protected ?int $orderDeleted;
    protected ?int $purchasesDeleted;

    /**
     * @param OrderData $orderData
     * @param PurchaseData $purchaseData
     * @param int $orderId
     */
    public function __construct(OrderData $orderData, PurchaseData $purchaseData, int $orderId)
    {
        $this->orderData = $orderData;
        $this->purchaseData = $purchaseData;
        $this->orderId = $orderId;
    }

    /**
     * Удалить заказ вместе с покупками.
     * @return bool
     * @throws FailedToDeleteOrder
     * @throws FailedToDeletePurchases
     */
    public function remove(): bool {
        $this->deleteOrder();
        $this->handleOrderDeleting();
        $this->deletePurchases();
        $this->checkPurchasesDeleting();

        return true;
    }

    protected function deleteOrder(){
        $this->orderDeleted = $this->orderData->deleteOrderById($this->orderId);
    }

    protected function handleOrderDeleting() {
        if (empty($this->orderDeleted)){
            throw new FailedToDeleteOrder();
        }
    }

    protected function deletePurchases(){
        $this->purchasesDeleted = $this->purchaseData->deleteOrderPurchases($this->orderId);
    }

    protected function checkPurchasesDeleting() {
        if (empty($this->purchasesDeleted)) {
            throw new FailedToDeletePurchases();
        }
    }
}