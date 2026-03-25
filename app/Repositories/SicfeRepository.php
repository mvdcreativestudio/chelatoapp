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

        } catch (\Throwable $e) {
            Log::error('[SICFE] Error al guardar la nota o actualizar balance: ' . $e->getMessage(), [
                'invoice_id' => $invoice->id,
            ]);
            throw new \Exception('Error al procesar la nota después de la emisión.');
        }
    }

    public function emitReceipt(int $invoiceId, ?string $emissionDate): void
    {
        // TODO: Implementar emisión de recibos vía SICFE
        throw new \Exception('Emisión de recibos vía SICFE aún no implementada.');
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
