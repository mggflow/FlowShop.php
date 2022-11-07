<?php

namespace MGGFLOW\FlowShop;

use MGGFLOW\FlowShop\Exceptions\FailedToCreateAccount;
use MGGFLOW\FlowShop\Exceptions\FailedToFindUserAccount;
use MGGFLOW\FlowShop\Exceptions\FailedToGetUserAccount;
use MGGFLOW\FlowShop\Interfaces\AccountData;

class ObtainAccount
{
    protected AccountData $accountData;
    protected int $userId;

    protected ?bool $accountNotExists;
    protected ?object $account;
    protected object $startAccount;
    protected ?int $newAccountId;

    public function __construct(AccountData $accountData, int $userId)
    {
        $this->accountData = $accountData;
        $this->userId = $userId;
    }

    public function obtain(){
        $this->checkAccountExistence();

        if ($this->accountNotExists) {
            $this->provideNewAccount();
        }

        $this->getExistsAccount();

        return $this->getObtainedAccount();
    }
    
    public function getObtainedAccount(){
        return $this->account;
    }

    protected function checkAccountExistence(){
        $this->accountNotExists = $this->accountData->userAccountNotExists($this->userId);
        if (is_null($this->accountNotExists)) {
            throw new FailedToFindUserAccount();
        }
    }

    protected function provideNewAccount() {
        $this->createStartAccount();
        $this->addNewAccount();
    }

    protected function createStartAccount() {
        $this->startAccount = (object)[
            'owner_id' => $this->userId,
            'balance' => 0,
            'created_at' => time()
        ];
    }

    protected function addNewAccount(){
        $this->newAccountId = $this->accountData->createAccount($this->startAccount);
        if (empty($this->newAccountId)) {
            throw new FailedToCreateAccount();
        }
    }

    protected function getExistsAccount() {
        $this->account = $this->accountData->getUserAccount($this->userId);
        if (is_null($this->account)) {
            throw new FailedToGetUserAccount();
        }
    }

}