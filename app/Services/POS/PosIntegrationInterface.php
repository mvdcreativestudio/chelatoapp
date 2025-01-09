<?php

namespace App\Services\POS;

interface PosIntegrationInterface {
  public function processTransaction(array $transactionData): array;
  public function checkTransactionStatus(array $transactionData): array;
  public function getResponses($responseCode);
  public function getToken();
  public function formatTransactionData(array $transactionData): array;
  public function reverseTransaction(array $transactionData): array;
  public function voidTransaction(array $transactionData): array;
  public function pollVoidStatus(array $transactionData): array;
  public function fetchTransactionHistory(array $queryData): array;

}

