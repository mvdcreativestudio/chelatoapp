<?php

namespace App\Services\Billing;

use App\Models\Order;
use App\Models\Store;
use App\Repositories\SicfeRepository;
use App\Services\Billing\Sicfe\SicfeDtoBuilder;
use Illuminate\Support\Facades\Log;

class SicfeBillingService implements BillingServiceInterface
{
    protected SicfeDtoBuilder $dtoBuilder;
    protected SicfeRepository $sicfeRepository;

    public function __construct(SicfeDtoBuilder $dtoBuilder, SicfeRepository $sicfeRepository)
    {
        $this->dtoBuilder = $dtoBuilder;
        $this->sicfeRepository = $sicfeRepository;
    }

    public function emitCFE(Order $order, ?float $amountToBill = null, ?int $payType = 1, ?string $adenda = null, ?string $emissionDate = null): void
    {
        Log::info('Iniciando emisión SICFE para orden: ' . $order->id);

        $order->loadMissing('client');

        $dto = $this->dtoBuilder->buildFromOrder($order, $amountToBill, $payType, $adenda, $emissionDate);
        $this->sicfeRepository->emitirFactura($dto);

        Log::info('Factura emitida correctamente por SICFE', ['order_id' => $order->id]);
    }

    public function consultarDatosRuc(string $ruc, Store $store): array
    {
        return $this->sicfeRepository->consultarDatosRuc($store, $ruc);
    }

    public function emitReceipt(int $invoiceId, ?string $emissionDate): void
    {
        $this->sicfeRepository->emitReceipt($invoiceId, $emissionDate);
    }

    public function emitNote(int $invoiceId, \App\Http\Requests\EmitNoteRequest $request): void
    {
        $this->sicfeRepository->emitNote($invoiceId, $request);
    }

    public function getCfePdf(int $cfeId): \Illuminate\Http\Response
    {
        return $this->sicfeRepository->getCfePdf($cfeId);
    }

    public function printCfePdf(int $cfeId)
    {
        return $this->sicfeRepository->printCfePdf($cfeId);
    }
}
