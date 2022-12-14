<?php

namespace MGGFLOW\FlowShop\Interfaces;

interface ProductData
{
    public function findProducts(array $categories, array $sortBy, int $offset, int $count): ?array;
    public function findMainPageProducts(int $offset, int $count): ?array;
    public function updateProductsAmounts(array $amountsByIds): ?int;
    public function findByIds(array $ids): ?array;
}