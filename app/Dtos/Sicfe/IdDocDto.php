<?php

namespace App\Dtos\Sicfe;

class IdDocDto
{
    public int $tipoCFE;
    public ?string $serie;
    public ?string $nro;
    public string $fechaEmision;
    public int $formaPago;
    public int $orderId;
    public int $storeId;
    public string $uuid;
    public int $montoBruto = 1;

    public function __construct(int $tipoCFE, string $fechaEmision, int $formaPago = 1, int $orderId = 0, int $storeId = 0, ?string $uuid = null)
    {
        $this->tipoCFE = $tipoCFE;
        $this->fechaEmision = $fechaEmision;
        $this->formaPago = $formaPago;
        $this->serie = null;
        $this->nro = null;
        $this->orderId = $orderId;
        $this->storeId = $storeId;
        $this->uuid = $uuid ?? (string) \Str::uuid();
    }
}
