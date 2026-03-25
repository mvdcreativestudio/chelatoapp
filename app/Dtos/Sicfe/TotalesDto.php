<?php

namespace App\Dtos\Sicfe;

class TotalesDto
{
    public string $moneda;
    public float $montoNoGravado;
    public float $montoExpoyAsim;
    public float $montoImpuestoPerc;
    public float $montoIvaSusp;
    public float $montoNetoIvaMin;
    public float $montoNetoIvaBasica;
    public float $montoNetoIvaOtra;
    public float $ivaTasaMin;
    public float $ivaTasaBasica;
    public float $montoIvaMin;
    public float $montoIvaBasica;
    public float $montoIvaOtra;
    public float $montoTotal;
    public float $montoTotalRetenido;
    public string $cantLineas;
    public float $montoNoFacturable;
    public float $montoPagar;

    public function __construct(
        string $moneda = 'UYU',
        float $montoNoGravado = 0,
        float $montoExpoyAsim = 0,
        float $montoImpuestoPerc = 0,
        float $montoIvaSusp = 0,
        float $montoNetoIvaMin = 0,
        float $montoNetoIvaBasica = 0,
        float $montoNetoIvaOtra = 0,
        float $ivaTasaMin = 10,
        float $ivaTasaBasica = 22,
        float $montoIvaMin = 0,
        float $montoIvaBasica = 0,
        float $montoIvaOtra = 0,
        float $montoTotal = 0,
        float $montoTotalRetenido = 0,
        string $cantLineas = '001',
        float $montoNoFacturable = 0,
        float $montoPagar = 0
    ) {
        $this->moneda = $moneda;
        $this->montoNoGravado = $montoNoGravado;
        $this->montoExpoyAsim = $montoExpoyAsim;
        $this->montoImpuestoPerc = $montoImpuestoPerc;
        $this->montoIvaSusp = $montoIvaSusp;
        $this->montoNetoIvaMin = $montoNetoIvaMin;
        $this->montoNetoIvaBasica = $montoNetoIvaBasica;
        $this->montoNetoIvaOtra = $montoNetoIvaOtra;
        $this->ivaTasaMin = $ivaTasaMin;
        $this->ivaTasaBasica = $ivaTasaBasica;
        $this->montoIvaMin = $montoIvaMin;
        $this->montoIvaBasica = $montoIvaBasica;
        $this->montoIvaOtra = $montoIvaOtra;
        $this->montoTotal = $montoTotal;
        $this->montoTotalRetenido = $montoTotalRetenido;
        $this->cantLineas = $cantLineas;
        $this->montoNoFacturable = $montoNoFacturable;
        $this->montoPagar = $montoPagar;
    }
}
