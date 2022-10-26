<?php

namespace MGGFLOW\FlowShop;

use MGGFLOW\FlowShop\Exceptions\FailedToFindUserOrders;
use MGGFLOW\FlowShop\Interfaces\OrderData;

class PullUserOrders
{
    protected OrderData $orderData;
    protected int $userId;
    protected int $offset;
    protected int $count;

    protected ?array $userOrders;

    /**
     * @param OrderData $orderData
     * @param int $userId
     * @param int $offset
     * @param int $count
     */
    public function __construct(OrderData $orderData, int $userId, int $offset = 0, int $count = 10)
    {
        $this->orderData = $orderData;

        $this->userId = $userId;
        $this->offset = $offset;
        $this->count = $count;
    }

    /**
     * Выбрать заказы пользователя.
     * @return array
     * @throws FailedToFindUserOrders
     */
    public function pull(): array {
        $this->findOrders();
        $this->checkFoundOrders();

        return $this->getUserOrders();
    }

    /**
     * Получить найденные заказы пользователя.
     * @return array|null
     */
    public function getUserOrders(): ?array
    {
        return $this->userOrders;
    }

    protected function findOrders(){
        $this->userOrders = $this->orderData->findUserOrders(
            $this->userId,
            $this->offset,
            $this->count
        );
    }

    protected function checkFoundOrders() {
        if (is_null($this->userOrders)){
            throw new FailedToFindUserOrders();
        }
    }
}