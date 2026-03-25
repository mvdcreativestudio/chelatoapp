<?php

namespace App\Services\Billing;

use App\Http\Requests\EmitNoteRequest;
use App\Models\Order;
use App\Models\Store;
use Illuminate\Http\Response;

interface BillingServiceInterface
{
    public function emitCFE(Order $order, ?float $amountToBill, ?int $payType, ?string $adenda, ?string $emissionDate): void;

    public function emitReceipt(int $invoiceId, ?string $emissionDate): void;

    public function emitNote(int $invoiceId, EmitNoteRequest $request): void;

    public function getCfePdf(int $cfeId): Response;

    public function printCfePdf(int $cfeId);

    public function consultarDatosRuc(string $ruc, Store $store): array;
}
