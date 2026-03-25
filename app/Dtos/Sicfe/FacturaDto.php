<?php

namespace App\Dtos\Sicfe;

class FacturaDto
{
    public CfeDto $cfe;
    public ?string $adenda;

    public function __construct(CfeDto $cfe, ?string $adenda = null)
    {
        $this->cfe = $cfe;
        $this->adenda = $adenda;
    }

    public function __get($name)
    {
        return $this->cfe->{$name} ?? null;
    }
}
