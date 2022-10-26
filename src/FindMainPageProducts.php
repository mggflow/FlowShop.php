<?php

namespace MGGFLOW\FlowShop;

use MGGFLOW\FlowShop\Exceptions\FailedToFindProducts;
use MGGFLOW\FlowShop\Interfaces\ProductData;

class FindMainPageProducts
{
    /**
     * Категории продуктов для поиска.
     * @var array
     */
    public array $categories = [];
    /**
     * Параметры сортировки продуктов.
     * @var array|string[]
     */
    public array $sortBy = ['created_at' => 'desc'];

    protected ProductData $productData;

    protected ?array $foundProducts;

    /**
     * @param ProductData $productData
     */
    public function __construct(ProductData $productData)
    {
        $this->productData = $productData;
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
        $this->foundProducts = $this->productData->findProducts($this->categories, $this->sortBy);
    }

    protected function handleFoundProducts() {
        if (is_null($this->foundProducts)){
            throw new FailedToFindProducts();
        }
    }
}