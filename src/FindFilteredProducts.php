<?php

namespace MGGFLOW\FlowShop;

use MGGFLOW\FlowShop\Exceptions\FailedToFindProducts;
use MGGFLOW\FlowShop\Interfaces\ProductData;

class FindFilteredProducts
{
    protected ProductData $productData;

    protected array $categories;
    protected array $sortBy;
    protected int $offset;
    protected int $count;

    protected ?array $foundProducts;

    /**
     * @param ProductData $productData
     * @param array $categories
     * @param array $sortBy
     * @param int $offset
     * @param int $count
     */
    public function __construct(
        ProductData $productData,
        array       $categories, array $sortBy,
        int         $offset, int $count
    )
    {
        $this->productData = $productData;

        $this->categories = $categories;
        $this->sortBy = $sortBy;
        $this->offset = $offset;
        $this->count = $count;
    }

    /**
     * Выполнить поиск продуктов с учетом настроенных фильтров.
     * @return array
     * @throws FailedToFindProducts
     */
    public function find(): array
    {
        $this->findProducts();
        $this->checkFoundProducts();

        return $this->getFoundProducts();
    }

    /**
     * Получить найденные продукты.
     * @return array|null
     */
    public function getFoundProducts(): ?array
    {
        return $this->foundProducts;
    }

    protected function findProducts(): void
    {
        $this->foundProducts = $this->productData->findProducts(
            $this->categories, $this->sortBy,
            $this->offset, $this->count
        );
    }

    protected function checkFoundProducts(): void
    {
        if (is_null($this->foundProducts)) {
            throw new FailedToFindProducts();
        }
    }
}