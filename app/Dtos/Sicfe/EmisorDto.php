<?php

namespace App\Dtos\Sicfe;

class EmisorDto
{
    public string $ruc;
    public string $razonSocial;
    public string $nombreComercial;
    public string $telefono;
    public string $correo;
    public string $sucursal;
    public int $codigoDgiSucursal;
    public string $direccion;
    public string $ciudad;
    public string $departamento;

    public function __construct(
        string $ruc,
        string $razonSocial,
        string $nombreComercial,
        string $telefono,
        string $correo,
        string $sucursal,
        int $codigoDgiSucursal,
        string $direccion,
        string $ciudad,
        string $departamento
    ) {
        $this->ruc = $this->formatRuc($ruc);
        $this->razonSocial = $razonSocial;
        $this->nombreComercial = $nombreComercial;
        $this->telefono = $telefono;
        $this->correo = $correo;
        $this->sucursal = $sucursal;
        $this->codigoDgiSucursal = (int)$codigoDgiSucursal;
        $this->direccion = $direccion;
        $this->ciudad = $ciudad;
        $this->departamento = $departamento;
    }

    private function formatRuc(string $ruc): string
    {
        $ruc = preg_replace('/[^0-9]/', '', $ruc);

        if (strlen($ruc) != 12) {
            throw new \InvalidArgumentException('El RUC debe tener 12 dígitos');
        }

        return $ruc;
    }
}
