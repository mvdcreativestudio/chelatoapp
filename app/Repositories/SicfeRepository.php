<?php

namespace App\Repositories;

use App\Dtos\Sicfe\FacturaDto;
use App\Dtos\Sicfe\CaeDataDto;
use App\Builders\Sicfe\FacturaXmlBuilder;
use Illuminate\Support\Facades\Log;
use App\Models\CFE;
use Illuminate\Support\Str;
use App\Services\Billing\Sicfe\SicfeSoapClient;
use Carbon\Carbon;
use App\Models\Store;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use App\Http\Requests\EmitNoteRequest;
use App\Services\Billing\Sicfe\SicfeDtoBuilder;

class SicfeRepository
{
    protected SicfeSoapClient $soapClient;
    protected SicfeDtoBuilder $dtoBuilder;

    public function __construct(SicfeSoapClient $soapClient, SicfeDtoBuilder $dtoBuilder)
    {
        $this->soapClient = $soapClient;
        $this->dtoBuilder = $dtoBuilder;
    }

    public function emitirFactura(FacturaDto $factura): array
    {
        Log::info('Comenzando con emitirFactura desde SicfeRepository', [
            'tipo_factura' => get_class($factura),
            'totales' => [
                'monto_total' => $factura->cfe->totales->montoTotal,
                'cant_lineas' => $factura->cfe->totales->cantLineas
            ]
        ]);

        $store = null;
        if (isset($factura->cfe->emisor->ruc)) {
            $store = Store::where('rut', $factura->cfe->emisor->ruc)->first();
        }
        if (!$store) {
            throw new \Exception('No se pudo determinar la tienda para emitir la factura.');
        }
        $credentials = $this->buildSicfeCredentialsFromStore($store);
        $soapClient = new SicfeSoapClient($credentials);

        $xml = app(FacturaXmlBuilder::class)->build($factura);
        Log::info('[SICFE] XML generado para envío a SOAP', ['xml' => $xml]);

        $referenciaErp = $factura->cfe->idDoc->uuid ?? Str::uuid();

        try {
            $response = $soapClient->enviarCFE($xml, $referenciaErp);
            Log::info('[SICFE] Respuesta recibida.', ['response' => $response]);

            $parsed = $response['parsed']->EnvioCFEResult ?? null;

            if (!$parsed || (isset($parsed->Codigo) && !in_array($parsed->Codigo, [0, 200]))) {
                $msg = isset($parsed->Descripcion) ? $parsed->Descripcion : 'Error desconocido de SICFE';
                throw new \Exception('Error SICFE: ' . $msg);
            }

            $factura->cfe->idDoc->serie = trim($parsed->IdCFE->Serie ?? '');
            $factura->cfe->idDoc->nro = $parsed->IdCFE->Numero ?? null;

            $factura->cfe->caeData = new CaeDataDto(
                cae_id: $parsed->datosCAE->nauto ?? '',
                dnro: $parsed->datosCAE->dnro ?? '',
                hnro: $parsed->datosCAE->hnro ?? '',
                fecVenc: isset($parsed->datosCAE->fvto) ? Carbon::parse($parsed->datosCAE->fvto)->format('Y-m-d') : '',
                hash: $parsed->hash ?? '',
                qrUrl: $parsed->LinkQR ?? ''
            );

            try {
                $dataToStore = $factura->cfe->toPersistenceArray(
                    $factura->cfe->idDoc->orderId,
                    $factura->cfe->idDoc->storeId
                );

                Log::info('[SICFE] Datos para guardar en CFE:', $dataToStore);

                $cfe = CFE::create($dataToStore);

                Log::info('[SICFE] CFE guardado correctamente en base de datos.', ['cfe_id' => $cfe->id]);
            } catch (\Throwable $e) {
                Log::error('[SICFE] Error al guardar el CFE: ' . $e->getMessage(), [
                    'trace' => $e->getTraceAsString()
                ]);
                throw new \Exception('Error al guardar el CFE en la base de datos: ' . $e->getMessage());
            }

            return $response;

        } catch (\Exception $e) {
            Log::error('[SICFE] Error al emitir factura: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getCfePdf(int $cfeId): \Illuminate\Http\Response
    {
        $cfe = CFE::findOrFail($cfeId);

        if (!$cfe->serie || !$cfe->nro || !$cfe->type || !$cfe->store?->rut) {
            throw new \Exception('El CFE no tiene los datos mínimos requeridos para obtener el PDF.');
        }

        $cfe->issuer_rut = $cfe->store->rut;
        $cfe->number = $cfe->nro;

        $credentials = $this->buildSicfeCredentialsFromStore($cfe->store);
        $soapClient = new SicfeSoapClient($credentials);

        try {
            $pdfBinary = $soapClient->obtenerCfePdf($cfe);

            if (!$pdfBinary) {
                throw new \Exception('La respuesta de SICFE no contiene un PDF válido.');
            }

            return response($pdfBinary, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="Factura_' . $cfe->serie . '_' . $cfe->nro . '.pdf"',
            ]);

        } catch (\Throwable $e) {
            Log::error('[SICFE] Error al obtener PDF del CFE: ' . $e->getMessage());
            throw new \Exception('Error al descargar el PDF desde SICFE. Detalle: ' . $e->getMessage());
        }
    }

    public function printCfePdf(int $cfeId): \Illuminate\Http\Response
    {
        $response = $this->getCfePdf($cfeId);
        $response->headers->set('Content-Disposition', 'inline; filename="CFE.pdf"');
        return $response;
    }

    public function consultarDatosRuc(Store $store, string $ruc): array
    {
        try {
            $password = Crypt::decryptString($store->billingCredential->password);

            $params = [
                'usuario' => $store->billingCredential->user,
                'clave'   => $password,
                'tenant'  => $store->billingCredential->tenant,
                'ruc'     => $ruc,
            ];

            $credentials = $this->buildSicfeCredentialsFromStore($store);
            $soapClient = new SicfeSoapClient($credentials);

            $response = $soapClient->obtenerDatosRucDgi($params);

            return $response;

        } catch (\Exception $e) {
            Log::error('SICFE - Error al desencriptar o consultar: ' . $e->getMessage(), [
                'store_id' => $store->id,
            ]);
            throw $e;
        }
    }

    public function emitNote(int $invoiceId, EmitNoteRequest $request): void
    {
        $invoice = CFE::findOrFail($invoiceId);

        if (!in_array($invoice->type, [101, 111])) {
            throw new \Exception('Solo se pueden emitir notas sobre eTicket o eFactura.');
        }

        $noteType = $request->noteType;
        $noteAmount = $request->noteAmount;
        $reason = $request->reason;
        $emissionDate = $request->emissionDate ?? now()->format('Y-m-d');

        if ($noteType === 'credit' && $noteAmount > $invoice->balance) {
            throw new \Exception('El monto de la nota de crédito no puede superar el saldo.');
        }

        $newBalance = $noteType === 'credit'
            ? $invoice->balance - $noteAmount
            : $invoice->balance + $noteAmount;

        if ($newBalance < 0) {
            throw new \Exception('El nuevo balance no puede ser negativo.');
        }

        $noteCfeType = match ($invoice->type) {
            101 => $noteType === 'credit' ? 102 : 103,
            111 => $noteType === 'credit' ? 112 : 113,
        };

        $dto = $this->dtoBuilder->buildNoteFromInvoice($invoice, $noteCfeType, $noteAmount, $reason, $emissionDate);

        $store = $invoice->store;
        $credentials = $this->buildSicfeCredentialsFromStore($store);
        $soapClient = new SicfeSoapClient($credentials);
        $xml = app(FacturaXmlBuilder::class)->build($dto);
        $referenciaErp = $dto->cfe->idDoc->uuid ?? Str::uuid();

        $response = $soapClient->enviarCFE($xml, $referenciaErp);

        Log::info('[SICFE] XML enviado para Nota', ['xml' => $xml]);
        Log::info('[SICFE] Respuesta completa', ['response' => $response]);

        try {
            $response['xml'] = $xml;
            $this->saveNoteCfe($invoice, $noteCfeType, $noteAmount, $reason, $response);

            $invoice->balance = $newBalance;
            $invoice->save();
            Log::info("Balance del CFE original {$invoice->id} actualizado.", ['nuevo_balance' => $invoice->balance]);

            // Si se emite nota de crédito, actualizar estado de pago de la orden
            if ($noteType === 'credit') {
                $order = $invoice->order;
                if ($order) {
                    $order->payment_status = $newBalance == 0 ? 'refunded' : 'partial_refunded';
                    $order->save();
                    Log::info("Orden #{$order->id} marcada como {$order->payment_status} por emisión de nota de crédito.");

                    // Si es reembolso total, reintegrar stock de los productos
                    if ($newBalance == 0) {
                        $this->restoreOrderStock($order);
                    }
                }
            }

        } catch (\Throwable $e) {
            Log::error('[SICFE] Error al guardar la nota o actualizar balance: ' . $e->getMessage(), [
                'invoice_id' => $invoice->id,
            ]);
            throw new \Exception('Error al procesar la nota después de la emisión.');
        }
    }

    /**
     * Reintegra el stock de los productos de una orden tras nota de crédito total.
     */
    private function restoreOrderStock(\App\Models\Order $order): void
    {
        $products = is_array($order->products) ? $order->products : json_decode($order->products, true);
        if (!is_array($products)) return;

        foreach ($products as $item) {
            $isComposite = isset($item['is_composite']) && ($item['is_composite'] === true || $item['is_composite'] == 1);
            $productModel = $isComposite
                ? \App\Models\CompositeProduct::find($item['id'])
                : \App\Models\Product::find($item['id']);

            if ($productModel && $productModel->stock !== null) {
                $oldStock = $productModel->stock;
                $productModel->stock += $item['quantity'];
                $productModel->save();
                \App\Models\StockMovement::record($productModel, 'credit_note', $item['quantity'], $oldStock, $productModel->stock, "NC total - Orden #{$order->id}");
            }
        }
        Log::info("Stock reintegrado por nota de crédito total de la orden #{$order->id}");
    }

    public function emitReceipt(int $invoiceId, ?string $emissionDate): void
    {
        throw new \Exception('Emisión de recibos vía SICFE aún no implementada.');
    }

    /**
     * Actualiza el estado DGI de todos los CFEs emitidos de una tienda que no tienen estado final.
     */
    public function updateCfeStatuses(Store $store): int
    {
        $credentials = $this->buildSicfeCredentialsFromStore($store);
        $soapClient = new SicfeSoapClient($credentials);

        // Obtener CFEs emitidos sin estado final (AE=aceptado, BE=rechazado, NA=no aplica)
        $finalStatuses = ['AE', 'BE', 'NA', 'PROCESSED_ACCEPTED', 'PROCESSED_REJECTED'];
        $cfes = CFE::where('store_id', $store->id)
            ->where('received', false)
            ->whereNotIn('status', $finalStatuses)
            ->whereNotNull('nro')
            ->whereNotNull('serie')
            ->get();

        $updated = 0;

        foreach ($cfes as $cfe) {
            try {
                $result = $soapClient->obtenerEstadoCFE([
                    'Numero'    => $cfe->nro,
                    'Serie'     => $cfe->serie,
                    'Tipo'      => $cfe->type,
                    'observado' => 1,
                    'rucemisor' => $store->rut,
                ]);

                $estado = $result['Estado'] ?? '';

                if ($estado !== '' && $estado !== $cfe->status) {
                    $cfe->status = $estado;
                    $cfe->save();
                    $updated++;
                    Log::info("[SICFE] Estado actualizado CFE #{$cfe->id}: {$estado}");
                }
            } catch (\Exception $e) {
                Log::warning("[SICFE] No se pudo consultar estado del CFE #{$cfe->id}: " . $e->getMessage());
            }
        }

        return $updated;
    }

    /**
     * Obtiene y almacena los CFEs recibidos (comprobantes de proveedores) desde SICFE.
     */
    public function processReceivedCfes(Store $store): ?array
    {
        $credentials = $this->buildSicfeCredentialsFromStore($store);
        $soapClient = new SicfeSoapClient($credentials);

        // Traer últimos 6 meses por defecto
        $fechaDesde = Carbon::now()->subMonths(6)->format('Y-m-d');
        $fechaHasta = Carbon::now()->format('Y-m-d');

        try {
            $cfesRecibidos = $soapClient->obtenerCFEsRecibidosExtendido($fechaDesde, $fechaHasta);

            if (empty($cfesRecibidos)) {
                Log::info('[SICFE] No se encontraron CFEs recibidos.');
                return [];
            }

            foreach ($cfesRecibidos as $cfeData) {
                $tipo = $cfeData['Tipo'] ?? null;
                $serie = $cfeData['Serie'] ?? null;
                $numero = $cfeData['Numero'] ?? null;

                if (!$tipo || !$serie || !$numero) {
                    Log::warning('[SICFE] CFE recibido sin datos mínimos, omitiendo.', $cfeData);
                    continue;
                }

                $cfeEntry = [
                    'store_id'          => $store->id,
                    'type'              => $tipo,
                    'serie'             => $serie,
                    'nro'               => $numero,
                    'caeNumber'         => $cfeData['NumeroAutorizacionCAE'] ?? null,
                    'caeRange'          => json_encode([
                        'desde' => $cfeData['DesdeNroCAE'] ?? null,
                        'hasta' => $cfeData['HastaNroCAE'] ?? null,
                    ]),
                    'caeExpirationDate' => $cfeData['FechaVencimientoCAE'] ?? null,
                    'total'             => $cfeData['MntPagar'] ?? 0,
                    'currency'          => $cfeData['Moneda'] ?? 'UYU',
                    'status'            => $cfeData['Estado'] ?? 'IN',
                    'balance'           => $cfeData['MntPagar'] ?? 0,
                    'received'          => true,
                    'emitionDate'       => $cfeData['FechaEmision'] ?? null,
                    'issuer_name'       => $cfeData['NombreComercial'] ?? $cfeData['RazonSocial'] ?? null,
                    'reason'            => $cfeData['RucEmisor'] ?? null,
                    'is_receipt'        => false,
                ];

                CFE::updateOrCreate(
                    [
                        'type'      => $tipo,
                        'serie'     => $serie,
                        'nro'       => $numero,
                        'store_id'  => $store->id,
                        'received'  => true,
                    ],
                    $cfeEntry
                );
            }

            return CFE::where('received', true)
                ->where('store_id', $store->id)
                ->orderBy('emitionDate', 'desc')
                ->get()
                ->toArray();

        } catch (\Exception $e) {
            Log::error('[SICFE] Error al procesar CFEs recibidos: ' . $e->getMessage());
            return null;
        }
    }

    protected function buildSicfeCredentialsFromStore(Store $store): array
    {
        $credential = $store->billingCredential;
        $provider = $store->billingProvider;

        if (!$credential || !$provider) {
            throw new \Exception('La tienda no tiene credenciales de facturación (BillingCredential/BillingProvider) configuradas.');
        }

        $endpoint = rtrim((string) ($provider->base_url ?? ''), '/');
        if ($endpoint === '') {
            $endpoint = (string) (config('services.sicfe.endpoint') ?? '');
        }
        if ($endpoint === '') {
            throw new \Exception(
                'No hay URL del servicio SICFE: definí SICFE_ENDPOINT en .env o base_url en el proveedor de facturación.'
            );
        }

        try {
            $clave = Crypt::decryptString($credential->password);
        } catch (DecryptException $e) {
            Log::warning('No se pudo desencriptar la contraseña SICFE. Store id: ' . $store->id);
            throw new \Exception(
                'La contraseña de facturación (SICFE) no pudo desencriptarse. Vuelva a guardar las credenciales SICFE en Integraciones.',
                0,
                $e
            );
        }

        return [
            'endpoint' => $endpoint,
            'usuario' => $credential->user ?? '',
            'clave' => $clave,
            'tenant' => $credential->tenant ?? '',
        ];
    }

    protected function saveNoteCfe(CFE $originalCfe, int $noteCfeType, float $amount, string $reason, array $response): void
    {
        $parsed = $response['parsed']->EnvioCFEResult ?? null;

        if (!$parsed) {
            throw new \Exception('La respuesta del servicio no contiene datos válidos.');
        }

        if (isset($parsed->Codigo) && !in_array($parsed->Codigo, [0, 200])) {
            $codigo = $parsed->Codigo;
            $descripcion = $parsed->Descripcion ?? 'Error desconocido de SICFE';
            throw new \Exception("Error SICFE al emitir nota (código {$codigo}): {$descripcion}");
        }

        if (!isset($parsed->IdCFE) || !isset($parsed->IdCFE->Numero)) {
            throw new \Exception('La respuesta de SICFE para la nota no incluye datos de IdCFE / Número.');
        }

        $data = [
            'order_id'          => $originalCfe->order_id,
            'store_id'          => $originalCfe->store_id,
            'type'              => $noteCfeType,
            'serie'             => trim($parsed->IdCFE->Serie ?? ''),
            'nro'               => $parsed->IdCFE->Numero,
            'caeNumber'         => $parsed->datosCAE->nauto ?? '',
            'caeRange'          => isset($parsed->datosCAE->dnro, $parsed->datosCAE->hnro)
                ? json_encode(['desde' => $parsed->datosCAE->dnro, 'hasta' => $parsed->datosCAE->hnro])
                : json_encode(['desde' => '1', 'hasta' => '1000000']),
            'caeExpirationDate' => isset($parsed->datosCAE->fvto) ? Carbon::parse($parsed->datosCAE->fvto)->format('Y-m-d') : null,
            'total'             => $amount,
            'currency'          => 'UYU',
            'status'            => 'CREATED_WITHOUT_CAE_NRO',
            'balance'           => $amount,
            'received'          => 0,
            'issuer_name'       => $originalCfe->issuer_name ?? null,
            'emitionDate'       => now()->format('Y-m-d'),
            'sentXmlHash'       => $parsed->hash ?? '',
            'securityCode'      => $parsed->hash ?? '',
            'qrUrl'             => $parsed->LinkQR ?? '',
            'main_cfe_id'       => $originalCfe->id,
            'cfeId'             => $parsed->IdCFE->CFEID ?? null,
            'reason'            => $reason,
            'is_receipt'        => 0,
        ];

        CFE::create($data);
    }
}
