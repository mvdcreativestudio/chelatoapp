<?php

namespace App\Services\Billing\Sicfe;

use App\Models\Order;
use App\Models\CompanySettings;
use App\Models\CFE;
use App\Dtos\Sicfe\FacturaDto;
use App\Dtos\Sicfe\CfeDto;
use App\Dtos\Sicfe\IdDocDto;
use App\Dtos\Sicfe\EmisorDto;
use App\Dtos\Sicfe\ReceptorDto;
use App\Dtos\Sicfe\TotalesDto;
use App\Dtos\Sicfe\ItemDto;
use Carbon\Carbon;

class SicfeDtoBuilder
{
    public function buildFromOrder(Order $order, ?float $amountToBill = null, ?int $payType = 1, ?string $adenda = null, ?string $emissionDate = null): FacturaDto
    {
        \Log::info('Iniciando buildFromOrder', [
            'order_id' => $order->id,
            'amount_to_bill' => $amountToBill,
        ]);

        $companySettings = CompanySettings::first();
        $pricesIncludeTax = $companySettings->prices_include_tax ?? 1;

        $items = [];
        $orderProducts = is_array($order->products)
            ? $order->products
            : json_decode($order->products, true);

        foreach ($orderProducts as $index => $product) {
            $isIvaMinimo = optional($order->store->billingCredential)->iva_minimo === 1;
            $indFact = $isIvaMinimo ? 16 : (int)($product['tax_rate_id'] ?? 3);

            $basePrice = $product['base_price'] ?? $product['price'];
            $quantity = $product['quantity'];

            $taxMultiplier = match ($indFact) {
                1 => 1.00,
                2 => 1.10,
                3 => 1.22,
                16 => 1.00,
                default => 1.22
            };

            $precioUnitario = $pricesIncludeTax
                ? $basePrice
                : round($basePrice * $taxMultiplier, 6);

            $montoFinal = round($precioUnitario * $quantity, 2);

            $productName = $product['name'];
            if (isset($product['variant_text']) && $product['variant_text']) {
                $productName = $product['name'] . ' - ' . $product['variant_text'];
            }

            $productName = $this->cleanProductName($productName);

            $items[] = new ItemDto(
                linea: str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                indFact: $indFact,
                nombre: $productName,
                cantidad: $quantity,
                unidadMedida: 'N/A',
                precioUnitario: $precioUnitario,
                monto: $montoFinal
            );
        }

        $netoBasica = 0;
        $ivaBasica = 0;
        $netoMinima = 0;
        $ivaMinima = 0;
        $montoNoGravado = 0;

        foreach ($items as $item) {
            $precioUnitSinIVA = match ($item->indFact) {
                3 => $item->precioUnitario / 1.22,
                2 => $item->precioUnitario / 1.10,
                16 => $item->precioUnitario,
                default => $item->precioUnitario
            };

            $montoNeto = round($precioUnitSinIVA * $item->cantidad, 2);
            $montoIVA = $item->monto - $montoNeto;

            match ($item->indFact) {
                3 => [ $netoBasica += $montoNeto, $ivaBasica += $montoIVA ],
                2 => [ $netoMinima += $montoNeto, $ivaMinima += $montoIVA ],
                default => $montoNoGravado += $item->monto
            };
        }

        $montoTotal = $netoBasica + $ivaBasica + $netoMinima + $ivaMinima + $montoNoGravado;

        $totales = new TotalesDto(
            moneda: 'UYU',
            montoNoGravado: round($montoNoGravado, 2),
            montoExpoyAsim: 0,
            montoImpuestoPerc: 0,
            montoIvaSusp: 0,
            montoNetoIvaMin: round($netoMinima, 2),
            montoNetoIvaBasica: round($netoBasica, 2),
            montoNetoIvaOtra: 0,
            ivaTasaMin: 10,
            ivaTasaBasica: 22,
            montoIvaMin: round($ivaMinima, 2),
            montoIvaBasica: round($ivaBasica, 2),
            montoIvaOtra: 0,
            montoTotal: round($montoTotal, 2),
            montoTotalRetenido: 0,
            cantLineas: str_pad(count($items), 3, '0', STR_PAD_LEFT),
            montoNoFacturable: 0,
            montoPagar: round($montoTotal, 2)
        );

        $fechaEmision = Carbon::parse($emissionDate ?? now())->format('Y-m-d');

        $receptor = null;
        $tipoCFE = 101;

        if ($order->client) {
            $client = $order->client;
            $isCompany = strtolower((string) ($client->type ?? '')) === 'company';

            if ($isCompany) {
                $rutDigits = preg_replace('/\D/', '', (string) ($client->rut ?? ''));
                // e-Factura (111) para receptor RUC; el RUC uruguayo son 12 dígitos (a veces se guarda con 11 sin el cero inicial)
                if ($rutDigits !== '') {
                    $ruc12 = $this->normalizeUruguayRuc12($rutDigits);
                    $tipoCFE = 111;
                    $receptor = new ReceptorDto(
                        tipoDocumento: 2,
                        documento: $ruc12,
                        razonSocial: (string) ($client->company_name ?? $client->name ?? ''),
                        direccion: $client->address ?? '',
                        ciudad: $client->city ?? '',
                        departamento: $client->state ?? '',
                        pais: 'UY'
                    );
                }
            } elseif (strtolower((string) ($client->type ?? '')) === 'individual') {
                $ciDigits = preg_replace('/\D/', '', (string) ($client->ci ?? ''));
                if (strlen($ciDigits) === 8) {
                    $receptor = new ReceptorDto(
                        tipoDocumento: 3,
                        documento: $ciDigits,
                        razonSocial: trim(($client->name ?? '') . ' ' . ($client->lastname ?? '')),
                        direccion: $client->address ?? '',
                        ciudad: $client->city ?? '',
                        departamento: $client->state ?? '',
                        pais: 'UY'
                    );
                }
            }
        }

        $isIvaMinimo = optional($order->store->billingCredential)->iva_minimo === 1;

        $idDoc = new IdDocDto(
            tipoCFE: $tipoCFE,
            fechaEmision: $fechaEmision,
            formaPago: (int)$payType,
            orderId: $order->id,
            storeId: $order->store_id,
        );

        $idDoc->montoBruto = $isIvaMinimo ? 3 : 1;

        $store = $order->store;
        $emisorCiudad = $store->city ?? $companySettings->city ?? '';
        $emisorDepartamento = $store->state ?? $companySettings->state ?? '';
        $emisor = new EmisorDto(
            ruc: $store->rut ?? '',
            razonSocial: $store->name ?? '',
            nombreComercial: $store->name ?? '',
            telefono: $store->phone ?? '',
            correo: $store->email ?? '',
            sucursal: $store->name ?? '',
            codigoDgiSucursal: (int)($store->billingCredential->branch_office ?? 1),
            direccion: $store->address ?? '',
            ciudad: $emisorCiudad,
            departamento: $emisorDepartamento
        );

        $cfe = new CfeDto(
            tipo: (string) $tipoCFE,
            idDoc: $idDoc,
            emisor: $emisor,
            receptor: $receptor,
            totales: $totales,
            items: $items
        );

        return new FacturaDto($cfe, $adenda);
    }

    public function buildNoteFromInvoice(CFE $invoice, int $noteCfeType, float $noteAmount, string $reason, string $emissionDate): FacturaDto
    {
        $store = $invoice->store;
        $moneda = 'UYU';

        $indFact = 3;

        $item = new ItemDto(
            linea: '001',
            indFact: $indFact,
            nombre: 'Nota sobre comprobante ' . $invoice->serie . '-' . $invoice->nro . ' (' . $reason . ')',
            cantidad: 1,
            unidadMedida: 'N/A',
            precioUnitario: $noteAmount,
            monto: $noteAmount
        );

        $netoBasica = 0.0;
        $ivaBasica = 0.0;
        $netoMinima = 0.0;
        $ivaMinima = 0.0;
        $montoNoGravado = 0.0;

        $precioUnitSinIVA = match ($item->indFact) {
            3 => $item->precioUnitario / 1.22,
            2 => $item->precioUnitario / 1.10,
            16 => $item->precioUnitario,
            1 => $item->precioUnitario,
            default => $item->precioUnitario
        };

        $montoNeto = round($precioUnitSinIVA * $item->cantidad, 2);
        $montoIVA = $item->monto - $montoNeto;

        switch ($item->indFact) {
            case 3:
                $netoBasica = $montoNeto;
                $ivaBasica = $montoIVA;
                break;
            case 2:
                $netoMinima = $montoNeto;
                $ivaMinima = $montoIVA;
                break;
            default:
                $montoNoGravado = $item->monto;
        }

        $montoTotal = round($netoBasica + $ivaBasica + $netoMinima + $ivaMinima + $montoNoGravado, 2);

        $totales = new TotalesDto(
            moneda: $moneda,
            montoNoGravado: round($montoNoGravado, 2),
            montoExpoyAsim: 0,
            montoImpuestoPerc: 0,
            montoIvaSusp: 0,
            montoNetoIvaMin: round($netoMinima, 2),
            montoNetoIvaBasica: round($netoBasica, 2),
            montoNetoIvaOtra: 0,
            ivaTasaMin: 10,
            ivaTasaBasica: 22,
            montoIvaMin: round($ivaMinima, 2),
            montoIvaBasica: round($ivaBasica, 2),
            montoIvaOtra: 0,
            montoTotal: $montoTotal,
            montoTotalRetenido: 0,
            cantLineas: '001',
            montoNoFacturable: 0,
            montoPagar: $montoTotal
        );

        $fechaFormateada = Carbon::parse($emissionDate)->format('Y-m-d');

        $idDoc = new IdDocDto(
            tipoCFE: $noteCfeType,
            fechaEmision: $fechaFormateada,
            formaPago: 1,
            orderId: $invoice->order_id,
            storeId: $invoice->store_id,
            uuid: \Str::uuid()
        );

        $companySettings = CompanySettings::first();
        $emisorCiudad = $store->city ?? $companySettings->city ?? '';
        $emisorDepartamento = $store->state ?? $companySettings->state ?? '';
        $emisor = new EmisorDto(
            ruc: $store->rut ?? '',
            razonSocial: $store->name ?? '',
            nombreComercial: $store->name ?? '',
            telefono: $store->phone ?? '',
            correo: $store->email ?? '',
            sucursal: $store->name ?? '',
            codigoDgiSucursal: (int)(optional($store->billingCredential)->branch_office ?? 1),
            direccion: $store->address ?? '',
            ciudad: $emisorCiudad,
            departamento: $emisorDepartamento
        );

        $invoice->load('order.client');
        $client = $invoice->order?->client;

        $tipoDocumento = 2;
        $documento = '';
        $razonSocial = '';
        $direccion = '';
        $ciudad = '';
        $departamento = '';

        if ($client) {
            if ($client->type === 'company') {
                $tipoDocumento = 2;
                $documento = preg_replace('/\D/', '', (string) ($client->rut ?? ''));
                if (strlen($documento) !== 12) {
                    $documento = '';
                }
            } elseif ($client->type === 'individual') {
                $tipoDocumento = 3;
                $documento = preg_replace('/\D/', '', (string) ($client->ci ?? ''));
                if (strlen($documento) !== 8) {
                    $documento = '';
                }
            }
            $razonSocial = $client->type === 'company'
                ? ($client->company_name ?? $client->name)
                : trim(($client->name ?? '') . ' ' . ($client->lastname ?? ''));
            $direccion = $client->address ?? '';
            $ciudad = $client->city ?? '';
            $departamento = $client->state ?? '';
        }

        if (($invoice->type === 111 || $noteCfeType >= 112) && empty($documento)) {
            throw new \RuntimeException('La e-factura requiere datos del receptor (RUT/CI). Verifique que la factura original tenga cliente con documento.');
        }

        $receptor = null;
        if (!empty($documento)) {
            $receptor = new ReceptorDto(
                tipoDocumento: $tipoDocumento,
                documento: $documento,
                razonSocial: $razonSocial,
                direccion: $direccion,
                ciudad: $ciudad,
                departamento: $departamento,
                pais: 'UY'
            );
        }

        $cfe = new CfeDto(
            tipo: (string)$noteCfeType,
            idDoc: $idDoc,
            emisor: $emisor,
            receptor: $receptor,
            totales: $totales,
            items: [$item]
        );

        $cfe->referencedCfeData = [
            'tipoCFE' => $invoice->type,
            'serie' => $invoice->serie,
            'nro' => $invoice->nro,
            'fechaEmision' => $invoice->emitionDate ? Carbon::parse($invoice->emitionDate)->format('Y-m-d') : Carbon::parse($emissionDate)->format('Y-m-d'),
        ];

        return new FacturaDto($cfe, $reason);
    }

    /**
     * RUC receptor (persona jurídica UY): 12 dígitos. Si vienen 11 u otro largo razonable, rellena a la izquierda.
     */
    private function normalizeUruguayRuc12(string $digitsOnly): string
    {
        $d = preg_replace('/\D/', '', $digitsOnly);
        if (strlen($d) > 12) {
            return substr($d, 0, 12);
        }

        return str_pad($d, 12, '0', STR_PAD_LEFT);
    }

    private function cleanProductName(string $productName): string
    {
        $cleanedName = preg_replace('/[^A-Za-z0-9\s\-.,]/', '', $productName);
        return mb_strimwidth($cleanedName, 0, 80);
    }
}
