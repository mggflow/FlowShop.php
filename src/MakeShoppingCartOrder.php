<?php

namespace MGGFLOW\FlowShop;

use MGGFLOW\FlowShop\Entities\Order;
use MGGFLOW\FlowShop\Exceptions\FailedToCreateOrder;
use MGGFLOW\FlowShop\Exceptions\FailedToCreatePurchases;
use MGGFLOW\FlowShop\Interfaces\OrderData;
use MGGFLOW\FlowShop\Interfaces\PurchaseData;
use MGGFLOW\IntCoder\Int2Code;

class MakeShoppingCartOrder
{
    protected OrderData $orderData;
    protected PurchaseData $purchaseData;
    protected object $order;
    protected array $purchases;

    protected ?int $orderId;
    protected Int2Code $coder;
    protected string $code;
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
        $this->prepareOrder();
        $this->createOrder();
        $this->checkOrderCreation();
        $this->setOrderCode();
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

    protected function prepareOrder(){
        $this->order->code = '';
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

    protected function setOrderCode(){
        $this->makeIdCoder();
        $this->genOrderCode();
        $this->updateOrderCode();
    }

    protected function makeIdCoder(){
        $this->coder = new Int2Code(Order::CODE_MIN, Order::CODE_MAX, Order::CODE_ALPHABET, Order::CODE_LENGTH);
    }

    protected function genOrderCode(){
        $this->code = $this->coder->encode($this->orderId);
    }

    protected function updateOrderCode() {
        $this->orderData->updateById($this->orderId, ['code' => $this->code]);
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