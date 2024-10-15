<?php

namespace App\Services\POS;

interface PosIntegrationInterface {
    public function processTransaction(array $transactionData): array;
    public function checkTransactionStatus(array $transactionData): array;
    public function getResponses($responseCode);
    public function getToken();
}
