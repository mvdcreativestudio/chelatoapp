<?php

namespace App\Dtos\Sicfe;

class ReceptorDto
{
    public int $tipoDocumento;
    public string $documento;
    public string $razonSocial;
    public string $nombreComercial;
    public string $telefono;
    public string $correo;
    public string $direccion;
    public string $ciudad;
    public string $departamento;
    public string $pais;

    public function __construct(
        int $tipoDocumento,
        string $documento,
        string $razonSocial,
        string $direccion,
        string $ciudad,
        string $departamento,
        string $pais
    ) {
        $this->tipoDocumento = $this->validateTipoDocumento($tipoDocumento);
        $this->documento = $this->formatDocumento($documento, $tipoDocumento);
        $this->razonSocial = $razonSocial;
        $this->nombreComercial = $razonSocial;
        $this->telefono = '';
        $this->correo = '';
        $this->direccion = $direccion;
        $this->ciudad = $ciudad;
        $this->departamento = $departamento;
        $this->pais = $pais;
    }

    private function validateTipoDocumento(int $tipoDocumento): int
    {
        $tiposValidos = [1, 2, 3, 4];
        if (!in_array($tipoDocumento, $tiposValidos)) {
            throw new \InvalidArgumentException('Tipo de documento no válido');
        }
        return $tipoDocumento;
    }

    private function formatDocumento(string $documento, int $tipoDocumento): string
    {
        $documento = preg_replace('/[^0-9]/', '', $documento);

        switch ($tipoDocumento) {
            case 1: // RUC
                if (strlen($documento) != 12) {
                    throw new \InvalidArgumentException('El RUC debe tener 12 dígitos');
                }
                break;
            case 3: // CI
                if (strlen($documento) != 8) {
                    throw new \InvalidArgumentException('La CI debe tener 8 dígitos');
                }
                break;
        }

        return $documento;
    }
}
