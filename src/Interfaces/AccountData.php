<?php

namespace MGGFLOW\FlowShop\Interfaces;

interface AccountData
{
    public function userAccountNotExists(int $userId): ?bool;
    public function getUserAccount(int $userId): ?object;
    public function createAccount(object $account): ?int;

}