<?php

namespace MGGFLOW\FlowShop;

use MGGFLOW\FlowShop\Exceptions\FailedToFindUserPurchases;
use MGGFLOW\FlowShop\Interfaces\PurchaseData;

class PullUserPurchases
{
    protected PurchaseData $purchaseData;
    protected int $userId;
    protected int $offset;
    protected int $count;

    protected ?array $userPurchases;

    /**
     * @param PurchaseData $purchaseData
     * @param int $userId
     * @param int $offset
     * @param int $count
     */
    public function __construct(PurchaseData $purchaseData, int $userId, int $offset = 0, int $count = 10)
    {
        $this->purchaseData = $purchaseData;

        $this->userId = $userId;
        $this->offset = $offset;
        $this->count = $count;
    }

    /**
     * @return array
     * @throws FailedToFindUserPurchases
     */
    public function pull(): array
    {
        $this->findPurchases();
        $this->checkFoundPurchases();

        return $this->getUserPurchases();
    }

    /**
     * Получить покупки пользователя.
     * @return array|null
     */
    public function getUserPurchases(): ?array
    {
        return $this->userPurchases;
    }

    protected function findPurchases()
    {
        $this->userPurchases = $this->purchaseData->findUserPurchases(
            $this->userId,
            $this->offset,
            $this->count
        );
    }

    protected function checkFoundPurchases()
    {
        if (is_null($this->userPurchases)) {
            throw new FailedToFindUserPurchases();
        }
    }
}