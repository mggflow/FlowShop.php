<?php

namespace MGGFLOW\FlowShop;

use MGGFLOW\FlowShop\Entities\Order;
use MGGFLOW\FlowShop\Entities\Purchase;
use MGGFLOW\FlowShop\Exceptions\FailedToCreateOrder;
use MGGFLOW\FlowShop\Exceptions\FailedToCreatePurchases;
use MGGFLOW\FlowShop\Exceptions\FailedToFindProducts;
use MGGFLOW\FlowShop\Exceptions\IncorrectPurchasesProducts;
use MGGFLOW\FlowShop\Exceptions\InvalidPurchase;
use MGGFLOW\FlowShop\Interfaces\OrderData;
use MGGFLOW\FlowShop\Interfaces\ProductData;
use MGGFLOW\FlowShop\Interfaces\PurchaseData;
use MGGFLOW\IntCoder\Int2Code;

class MakeShoppingCartOrder
{
    protected ProductData $productData;
    protected OrderData $orderData;
    protected PurchaseData $purchaseData;
    protected object $order;
    protected array $purchases;

    protected array $productsIds;
    protected ?array $products;

    protected ?int $orderId;
    protected Int2Code $coder;
    protected ?int $purchasesCreated;

    /**
     * @param ProductData $productData
     * @param OrderData $orderData
     * @param PurchaseData $purchaseData
     * @param object $order
     * @param array $purchases
     */
    public function __construct(
        ProductData $productData, OrderData $orderData, PurchaseData $purchaseData,
        object      $order, array $purchases
    )
    {
        $this->productData = $productData;
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
        $this->loadPurchasesProducts();
        $this->validatePurchases();
        $this->preSetOrderFields();
        $this->createOrder();
        $this->checkOrderCreation();
        $this->fixatePurchases();
        $this->supplementOrderFields();
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

    protected function loadPurchasesProducts()
    {
        $loader = new LoadPurchasesProducts($this->productData, $this->purchases);
        $this->products = $loader->load();
    }

    protected function validatePurchases()
    {
        if (count($this->purchases) != count($this->products)) {
            throw new IncorrectPurchasesProducts();
        }

        foreach ($this->purchases as $purchase) {
            $purchaseProduct = $this->products[$purchase->product_id];

            if ($purchaseProduct->archival) {
                throw new IncorrectPurchasesProducts();
            }

            Purchase::validate($purchase, $purchaseProduct);
        }
    }

    protected function preSetOrderFields()
    {
        $this->resetOrderCode();
        $this->resetOrderPrice();
    }

    protected function resetOrderCode()
    {
        $this->order->code = '';
    }

    protected function resetOrderPrice()
    {
        $this->order->price = 0;
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

    protected function fixatePurchases()
    {
        $purchases = new OperatePurchasesAmounts($this->purchases, $this->productData, $this->products);
        $result = $purchases->subAmounts();
        $this->purchases = $result['purchases'];
    }

    protected function supplementOrderFields()
    {
        $this->postSetOrderFields();
        $this->updateOrderFields();
    }

    protected function postSetOrderFields()
    {
        $this->makeIdCoder();
        $this->genOrderCode();
        $this->calcOrderPrice();
    }

    protected function makeIdCoder()
    {
        $this->coder = new Int2Code(Order::CODE_MIN, Order::CODE_MAX, Order::CODE_ALPHABET, Order::CODE_LENGTH);
    }

    protected function genOrderCode()
    {
        $this->order->code = $this->coder->encode($this->orderId);
    }

    protected function calcOrderPrice()
    {
        $calculator = new CalcOrderPrice($this->purchases, $this->products);
        $calculationResult = $calculator->calc();

        $this->purchases = $calculationResult['purchases'];
        $this->order->price = $calculationResult['orderPrice'];
    }

    protected function updateOrderFields()
    {
        $this->orderData->updateById($this->orderId, [
            'code' => $this->order->code,
            'price' => $this->order->price
        ]);
    }

    protected function createPurchases()
    {
        $this->purchasesCreated = $this->purchaseData->createAnyPurchases($this->purchases);
    }

    protected function checkPurchasesCreation()
    {
        if (is_null($this->purchasesCreated)) {
            $this->orderData->deleteOrderById($this->orderId);
            $this->freePurchasesAmounts();

            throw new FailedToCreatePurchases();
        }
    }

    protected function freePurchasesAmounts()
    {
        $purchases = new OperatePurchasesAmounts($this->purchases, $this->productData, null);
        $purchases->addAmounts();
    }
}