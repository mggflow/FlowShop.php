<?php

namespace MGGFLOW\FlowShop;

use MGGFLOW\FlowShop\Exceptions\FailedToFindProducts;
use MGGFLOW\FlowShop\Interfaces\ProductData;

class FindFilteredProducts
{
    protected array $categories;
    protected array $sortBy;
    protected ProductData $productData;

    protected ?array $foundProducts;

    /**
     * @param ProductData $productData
     * @param array $categories
     * @param array $sortBy
     */
    public function __construct(ProductData $productData, array $categories, array $sortBy)
    {
        $this->productData = $productData;

        $this->categories = $categories;
        $this->sortBy = $sortBy;
    }

    /**
     * Выполнить поиск продуктов с учетом настроенных фильтров.
     * @return array
     * @throws FailedToFindProducts
     */
    public function find(): array {
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

    protected function findProducts() {
        $this->foundProducts = $this->productData->findProducts($this->categories, $this->sortBy);
    }

    protected function checkFoundProducts() {
        if (is_null($this->foundProducts)){
            throw new FailedToFindProducts();
        }
    }
}