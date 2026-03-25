<?php

namespace App\Dtos\Sicfe;

class CaeDataDto
{
    public ?string $cae_id;
    public ?string $dnro;
    public ?string $hnro;
    public ?string $fecVenc;
    public ?string $hash;
    public ?string $qrUrl;

    public function __construct(
        ?string $cae_id = null,
        ?string $dnro = null,
        ?string $hnro = null,
        ?string $fecVenc = null,
        ?string $hash = null,
        ?string $qrUrl = null
    ) {
        $this->cae_id = $cae_id;
        $this->dnro = $dnro;
        $this->hnro = $hnro;
        $this->fecVenc = $fecVenc;
        $this->hash = $hash;
        $this->qrUrl = $qrUrl;
    }
}
