<?php

namespace MGGFLOW\FlowShop;

class PayOrder
{

    protected int $orderId;

    /**
     * @param int $orderId
     */
    public function __construct(int $orderId)
    {
        $this->orderId = $orderId;
    }

    /**
     * Подготовить ссылку на страницу оплаты заказа.
     * @return string
     */
    public function pay(): string {
        // prepare transaction fields
        // send request to Paygate
        // return Paygate result redirect url
        return '';
    }
}