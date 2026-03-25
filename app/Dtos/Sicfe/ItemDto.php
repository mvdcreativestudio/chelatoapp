<?php

namespace App\Dtos\Sicfe;

class ItemDto
{
    public string $linea;
    public int $indFact;
    public string $nombre;
    public float $cantidad;
    public string $unidadMedida;
    public float $precioUnitario;
    public float $monto;

    public function __construct(
        string $linea = '001',
        int $indFact = 3,
        string $nombre = '',
        float $cantidad = 1,
        string $unidadMedida = 'N/A',
        float $precioUnitario = 0,
        float $monto = 0
    ) {
        $this->linea = $linea;
        $this->indFact = $indFact;
        $this->nombre = $nombre;
        $this->cantidad = $cantidad;
        $this->unidadMedida = $unidadMedida;
        $this->precioUnitario = $precioUnitario;
        $this->monto = $monto;
    }
}
