<?php

namespace MGGFLOW\FlowShop\Interfaces;

interface OrderData
{
    public function createOrder(object $order): ?int;
    public function deleteOrderById(int $id): ?int;
    public function findUserOrders(int $userId, int $offset, int $count): ?array;
}