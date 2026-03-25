<?php

namespace App\Services\Billing;

use App\Http\Requests\EmitNoteRequest;
use App\Models\Order;
use App\Models\Store;
use App\Repositories\AccountingRepository;
use Illuminate\Http\Response;

class PymoBillingService implements BillingServiceInterface
{
    protected AccountingRepository $accountingRepository;

    public function __construct(AccountingRepository $accountingRepository)
    {
        $this->accountingRepository = $accountingRepository;
    }

    public function emitCFE(Order $order, ?float $amountToBill, ?int $payType, ?string $adenda, ?string $emissionDate): void
    {
        $this->accountingRepository->emitCFE($order, $amountToBill, $payType);
    }

    public function emitReceipt(int $invoiceId, ?string $emissionDate): void
    {
        $this->accountingRepository->emitReceipt($invoiceId);
    }

    public function emitNote(int $invoiceId, EmitNoteRequest $request): void
    {
        $this->accountingRepository->emitNote($invoiceId, $request);
    }

    public function getCfePdf(int $cfeId): Response
    {
        return $this->accountingRepository->getCfePdf($cfeId);
    }

    public function printCfePdf(int $cfeId)
    {
        return $this->accountingRepository->getCfePdf($cfeId);
    }

    public function consultarDatosRuc(string $ruc, Store $store): array
    {
        return [];
    }
}
