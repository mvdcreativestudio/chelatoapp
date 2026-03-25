<?php

namespace App\Builders\Sicfe;

use App\Dtos\Sicfe\FacturaDto;
use Carbon\Carbon;

class FacturaXmlBuilder
{
    public function build(FacturaDto $factura): string
    {
        $facturaXml = $this->buildFacturaBlock($factura);

        return <<<XML
<nsAdenda:CFE_Adenda xmlns:nsAdenda="http://cfe.dgi.gub.uy">
    $facturaXml
    <nsAdenda:Adenda>{$factura->adenda}</nsAdenda:Adenda>
</nsAdenda:CFE_Adenda>
XML;
    }

    private function buildFacturaBlock(FacturaDto $factura): string
    {
        $doc = new \DOMDocument('1.0', 'UTF-8');
        $doc->formatOutput = true;

        $cfe = $doc->createElementNS('http://cfe.dgi.gub.uy', 'nsAd:CFE');
        $cfe->setAttribute('version', '1.0');

        $idDoc = $factura->cfe->idDoc;

        $tipoNodo = match ((int)$idDoc->tipoCFE) {
            101, 102, 103 => 'eTck',
            111, 112, 113 => 'eFact',
            default => 'eTck'
        };
        $contenidoCfe = $doc->createElement('nsAd:' . $tipoNodo);
        $cfe->appendChild($contenidoCfe);

        $encabezado = $doc->createElement('nsAd:Encabezado');
        $contenidoCfe->appendChild($encabezado);

        $idDocEl = $doc->createElement('nsAd:IdDoc');
        $idDocEl->appendChild($doc->createElement('nsAd:TipoCFE', $idDoc->tipoCFE));
        $idDocEl->appendChild($doc->createElement('nsAd:Serie', $idDoc->serie ?? 'A'));
        $idDocEl->appendChild($doc->createElement('nsAd:Nro', $idDoc->nro ?? 1));
        $fechaHoraEmision = Carbon::parse($idDoc->fechaEmision)->format('Y-m-d');
        $idDocEl->appendChild($doc->createElement('nsAd:FchEmis', $fechaHoraEmision));
        $idDocEl->appendChild($doc->createElement('nsAd:MntBruto', '1'));
        $idDocEl->appendChild($doc->createElement('nsAd:FmaPago', $idDoc->formaPago));
        $encabezado->appendChild($idDocEl);

        $emisor = $factura->cfe->emisor;

        $emisorEl = $doc->createElement('nsAd:Emisor');
        $emisorEl->appendChild($doc->createElement('nsAd:RUCEmisor', $emisor->ruc));
        $emisorEl->appendChild($doc->createElement('nsAd:RznSoc', $emisor->razonSocial));
        $emisorEl->appendChild($doc->createElement('nsAd:NomComercial', $emisor->nombreComercial));
        $emisorEl->appendChild($doc->createElement('nsAd:Telefono', $emisor->telefono ?? ''));
        $emisorEl->appendChild($doc->createElement('nsAd:CorreoEmisor', $emisor->correo ?? ''));
        $emisorEl->appendChild($doc->createElement('nsAd:EmiSucursal', $emisor->sucursal ?? ''));
        $emisorEl->appendChild($doc->createElement('nsAd:CdgDGISucur', $emisor->codigoDgiSucursal));
        $emisorEl->appendChild($doc->createElement('nsAd:DomFiscal', $emisor->direccion ?? ''));
        $emisorEl->appendChild($doc->createElement('nsAd:Ciudad', $emisor->ciudad ?? ''));
        $emisorEl->appendChild($doc->createElement('nsAd:Departamento', $emisor->departamento ?? ''));
        $encabezado->appendChild($emisorEl);

        $receptor = $factura->cfe->receptor;

        if ($receptor) {
            $receptorEl = $doc->createElement('nsAd:Receptor');
            $receptorEl->appendChild($doc->createElement('nsAd:TipoDocRecep', $receptor->tipoDocumento));
            $receptorEl->appendChild($doc->createElement('nsAd:CodPaisRecep', $receptor->pais));
            $receptorEl->appendChild($doc->createElement('nsAd:DocRecep', $receptor->documento));
            $receptorEl->appendChild($doc->createElement('nsAd:RznSocRecep', $receptor->razonSocial));
            $receptorEl->appendChild($doc->createElement('nsAd:DirRecep', $receptor->direccion ?? ''));
            $receptorEl->appendChild($doc->createElement('nsAd:CiudadRecep', $receptor->ciudad ?? ''));
            $receptorEl->appendChild($doc->createElement('nsAd:DeptoRecep', $receptor->departamento ?? ''));
            $receptorEl->appendChild($doc->createElement('nsAd:CP', '0'));
            $encabezado->appendChild($receptorEl);
        }

        $totales = $factura->cfe->totales;

        $totalesEl = $doc->createElement('nsAd:Totales');
        $totalesEl->appendChild($doc->createElement('nsAd:TpoMoneda', $totales->moneda));
        $totalesEl->appendChild($doc->createElement('nsAd:MntNoGrv', number_format($totales->montoNoGravado, 2, '.', '')));
        $totalesEl->appendChild($doc->createElement('nsAd:MntExpoyAsim', '0.00'));
        $totalesEl->appendChild($doc->createElement('nsAd:MntImpuestoPerc', '0.00'));
        $totalesEl->appendChild($doc->createElement('nsAd:MntIVaenSusp', '0.00'));
        $totalesEl->appendChild($doc->createElement('nsAd:MntNetoIvaTasaMin', number_format($totales->montoNetoIvaMin, 2, '.', '')));
        $totalesEl->appendChild($doc->createElement('nsAd:MntNetoIVATasaBasica', number_format($totales->montoNetoIvaBasica, 2, '.', '')));
        $totalesEl->appendChild($doc->createElement('nsAd:MntNetoIVAOtra', '0.00'));
        $totalesEl->appendChild($doc->createElement('nsAd:IVATasaMin', number_format($totales->ivaTasaMin, 2, '.', '')));
        $totalesEl->appendChild($doc->createElement('nsAd:IVATasaBasica', number_format($totales->ivaTasaBasica, 2, '.', '')));
        $totalesEl->appendChild($doc->createElement('nsAd:MntIVATasaMin', number_format($totales->montoIvaMin, 2, '.', '')));
        $totalesEl->appendChild($doc->createElement('nsAd:MntIVATasaBasica', number_format($totales->montoIvaBasica, 2, '.', '')));
        $totalesEl->appendChild($doc->createElement('nsAd:MntIVAOtra', '0.00'));
        $totalesEl->appendChild($doc->createElement('nsAd:MntTotal', number_format($totales->montoTotal, 2, '.', '')));
        $totalesEl->appendChild($doc->createElement('nsAd:MntTotRetenido', '0.00'));
        $totalesEl->appendChild($doc->createElement('nsAd:CantLinDet', $totales->cantLineas));
        $totalesEl->appendChild($doc->createElement('nsAd:MontoNF', '0.00'));
        $totalesEl->appendChild($doc->createElement('nsAd:MntPagar', number_format($totales->montoPagar, 2, '.', '')));
        $encabezado->appendChild($totalesEl);

        $detalle = $doc->createElement('nsAd:Detalle');
        foreach ($factura->cfe->items as $index => $item) {
            $itemEl = $doc->createElement('nsAd:Item');
            $itemEl->appendChild($doc->createElement('nsAd:NroLinDet', $item->linea));
            $itemEl->appendChild($doc->createElement('nsAd:IndFact', $item->indFact));
            $itemEl->appendChild($doc->createElement('nsAd:NomItem', $item->nombre));
            $itemEl->appendChild($doc->createElement('nsAd:Cantidad', number_format($item->cantidad, 2, '.', '')));
            $itemEl->appendChild($doc->createElement('nsAd:UniMed', $item->unidadMedida));
            $itemEl->appendChild($doc->createElement('nsAd:PrecioUnitario', number_format($item->precioUnitario, 6, '.', '')));
            $itemEl->appendChild($doc->createElement('nsAd:MontoItem', number_format($item->monto, 2, '.', '')));
            $detalle->appendChild($itemEl);
        }
        $contenidoCfe->appendChild($detalle);

        // Referencia para notas de crédito/débito
        if (in_array($factura->cfe->idDoc->tipoCFE, [102, 103, 112, 113])) {
            $referenciaPadre = $doc->createElement('nsAd:Referencia');
            $referenciaHija = $doc->createElement('nsAd:Referencia');

            $montoRef = number_format($factura->cfe->totales->montoTotal, 2, '.', '');
            $monedaRef = $factura->cfe->totales->moneda ?? 'UYU';

            $referenciaHija->appendChild($doc->createElement('nsAd:NroLinRef', '1'));
            $referenciaHija->appendChild($doc->createElement('nsAd:TpoDocRef', $factura->cfe->referencedCfeData['tipoCFE']));
            $referenciaHija->appendChild($doc->createElement('nsAd:Serie', $factura->cfe->referencedCfeData['serie']));
            $referenciaHija->appendChild($doc->createElement('nsAd:NroCFERef', $factura->cfe->referencedCfeData['nro']));
            $referenciaHija->appendChild($doc->createElement('nsAd:RazonRef', $factura->adenda));
            $fechaRef = Carbon::parse($factura->cfe->referencedCfeData['fechaEmision'])->format('Y-m-d');
            $referenciaHija->appendChild($doc->createElement('nsAd:FechaCFEref', $fechaRef));
            $referenciaHija->appendChild($doc->createElement('nsAd:MntCFEref', $montoRef));
            $referenciaHija->appendChild($doc->createElement('nsAd:TpoMonedaRef', $monedaRef));
            $referenciaHija->appendChild($doc->createElement('nsAd:TpoCambioRef', '1.000000'));

            $referenciaPadre->appendChild($referenciaHija);
            $contenidoCfe->appendChild($referenciaPadre);
        }

        $caeData = $doc->createElement('nsAd:CAEData');
        $caeData->appendChild($doc->createElement('nsAd:CAE_ID'));
        $caeData->appendChild($doc->createElement('nsAd:DNro'));
        $caeData->appendChild($doc->createElement('nsAd:HNro'));
        $caeData->appendChild($doc->createElement('nsAd:FecVenc'));
        $contenidoCfe->appendChild($caeData);

        $doc->appendChild($cfe);
        return $doc->saveXML($cfe);
    }
}
