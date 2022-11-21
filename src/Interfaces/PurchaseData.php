<?php

namespace MGGFLOW\FlowShop\Interfaces;

interface PurchaseData
{
    public function createAnyPurchases(array $purchases): ?int;

    public function findUserPurchases(int $userId, int $offset, int $count): ?array;

    public function deleteOrderPurchases(int $orderId): ?int;
}