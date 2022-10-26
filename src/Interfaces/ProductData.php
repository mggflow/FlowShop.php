<?php

namespace MGGFLOW\FlowShop\Interfaces;

interface ProductData
{
    public function findProducts(array $categories, array $sortBy): ?array;
}