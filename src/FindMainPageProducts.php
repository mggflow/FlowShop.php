<?php

namespace MGGFLOW\FlowShop;

use MGGFLOW\FlowShop\Exceptions\FailedToFindProducts;
use MGGFLOW\FlowShop\Interfaces\ProductData;

class FindMainPageProducts
{
    protected ProductData $productData;

    protected ?array $foundProducts;
    protected int $offset;
    protected int $count;

    /**
     * @param ProductData $productData
     */
    public function __construct(ProductData $productData, int $offset, int $count)
    {
        $this->productData = $productData;
        $this->offset = $offset;
        $this->count = $count;
    }

    /**
     * Найти продукты для главной страницы.
     * @return array|null
     * @throws FailedToFindProducts
     */
    public function find() {
        $this->findProducts();
        $this->handleFoundProducts();

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

    protected function findProducts(){
        $this->foundProducts = $this->productData->findMainPageProducts($this->offset, $this->count);
    }

    protected function handleFoundProducts() {
        if (is_null($this->foundProducts)){
            throw new FailedToFindProducts();
        }
    }
}