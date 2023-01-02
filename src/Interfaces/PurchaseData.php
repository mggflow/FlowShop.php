<?php

namespace MGGFLOW\FlowShop\Interfaces;

interface PurchaseData
{
    public function createAnyPurchases(array $purchases): ?int;

    public function findUserPurchases(int $userId, int $offset, int $count): ?array;
    public function findForOrder(int $orderId): ?array;
    public function updateWithIds(array $purchases): ?array;
    public function deleteOrderPurchases(int $orderId): ?int;
    public function deleteById(int $purchaseId): bool;
}