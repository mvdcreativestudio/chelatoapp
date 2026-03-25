<?php

namespace App\Dtos\Sicfe;

class CfeDto
{
    public string $tipo;
    public IdDocDto $idDoc;
    public EmisorDto $emisor;
    public ?ReceptorDto $receptor;
    public TotalesDto $totales;
    public array $items;
    public ?CaeDataDto $caeData;
    public ?string $currency = 'UYU';
    public ?string $status = null;
    public ?bool $isReceipt = false;
    public ?string $issuerName = null;
    public ?int $received = 0;
    public ?array $referencedCfeData = null;

    public function __construct(
        string $tipo,
        IdDocDto $idDoc,
        EmisorDto $emisor,
        ?ReceptorDto $receptor,
        TotalesDto $totales,
        array $items = [],
        ?CaeDataDto $caeData = null
    ) {
        $this->tipo = $tipo;
        $this->idDoc = $idDoc;
        $this->emisor = $emisor;
        $this->receptor = $receptor;
        $this->totales = $totales;
        $this->items = $items;
        $this->caeData = $caeData;
    }

    public function toPersistenceArray(int $orderId, int $storeId): array
    {
        return [
            'order_id' => $orderId,
            'store_id' => $storeId,
            'type' => $this->tipo,
            'serie' => $this->idDoc->serie,
            'nro' => $this->idDoc->nro,
            'caeNumber' => $this->caeData?->cae_id ?? '',
            'caeRange' => json_encode([
                'desde' => $this->caeData?->dnro ?? '',
                'hasta' => $this->caeData?->hnro ?? '',
            ]),
            'caeExpirationDate' => $this->caeData?->fecVenc ?? '',
            'total' => $this->totales->montoTotal,
            'balance' => $this->totales->montoTotal,
            'currency' => $this->currency ?? 'UYU',
            'status' => $this->status ?? ($this->tipo === '101' ? 'SCHEDULED_WITHOUT_CAE_NRO' : 'CREATED_WITHOUT_CAE_NRO'),
            'emitionDate' => $this->idDoc->fechaEmision,
            'sentXmlHash' => $this->caeData?->hash ?? '',
            'securityCode' => $this->caeData?->hash ?? '',
            'qrUrl' => $this->caeData?->qrUrl ?? '',
            'cfeId' => null,
            'reason' => null,
            'main_cfe_id' => null,
            'received' => $this->received ?? 0,
            'is_receipt' => $this->isReceipt ?? false,
            'issuer_name' => $this->issuerName,
        ];
    }
}
