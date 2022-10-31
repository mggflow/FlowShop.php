<?php

namespace MGGFLOW\FlowShop;

use MGGFLOW\FlowShop\Exceptions\FailedToCreateOrder;
use MGGFLOW\FlowShop\Exceptions\FailedToCreatePurchases;
use MGGFLOW\FlowShop\Interfaces\OrderData;
use MGGFLOW\FlowShop\Interfaces\PurchaseData;

class MakeShoppingCartOrder
{
    protected OrderData $orderData;
    protected PurchaseData $purchaseData;
    protected object $order;
    protected array $purchases;

    protected ?int $orderId;
    protected ?int $purchasesCreated;

    /**
     * @param OrderData $orderData
     * @param PurchaseData $purchaseData
     * @param object $order
     * @param array $purchases
     */
    public function __construct(
        OrderData $orderData, PurchaseData $purchaseData,
        object    $order, array $purchases
    )
    {
        $this->orderData = $orderData;
        $this->purchaseData = $purchaseData;

        $this->order = $order;
        $this->purchases = $purchases;
    }

    /**
     * Создать заказ по продуктам корзины.
     * @return int
     * @throws FailedToCreateOrder
     * @throws FailedToCreatePurchases
     */
    public function make(): int
    {
        $this->createOrder();
        $this->checkOrderCreation();
        $this->createPurchases();
        $this->checkPurchasesCreation();

        return $this->getOrderId();
    }

    /**
     * Получить Id созданного заказа.
     * @return int|null
     */
    public function getOrderId(): ?int
    {
        return $this->orderId;
    }

    protected function createOrder()
    {
        $this->orderId = $this->orderData->createOrder($this->order);
    }

    protected function checkOrderCreation()
    {
        if (is_null($this->orderId)) {
            throw new FailedToCreateOrder();
        }
    }

    protected function createPurchases()
    {
        $this->purchasesCreated = $this->purchaseData->createAnyPurchases($this->purchases);
    }

    protected function checkPurchasesCreation()
    {
        if (is_null($this->purchasesCreated)) {
            $this->orderData->deleteOrderById($this->orderId);
            throw new FailedToCreatePurchases();
        }
    }
}