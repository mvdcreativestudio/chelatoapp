<?php

namespace App\Services\POS;

class PosService
{
    protected $posIntegration;

    public function __construct(PosIntegrationInterface $posIntegration)
    {
        $this->posIntegration = $posIntegration;
    }

    public function processTransaction(array $transactionData): array
    {
        return $this->posIntegration->processTransaction($transactionData);
    }

    public function checkTransactionStatus(array $transactionData)
    {
        return $this->posIntegration->checkTransactionStatus($transactionData);
    }

    public function getResponses()
    {
        return $this->posIntegration->getResponses();
    }

    public function getScanntechToken()
    {
        return $this->posIntegration->getToken();
    }
}
