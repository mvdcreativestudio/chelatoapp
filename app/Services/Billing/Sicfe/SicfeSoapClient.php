<?php

namespace App\Services\Billing\Sicfe;

use Illuminate\Support\Facades\Log;

class SicfeSoapClient
{
    protected string $endpoint;
    protected string $usuario;
    protected string $clave;
    protected string $tenant;

    public function __construct(array $credentials)
    {
        $this->endpoint = (string) ($credentials['endpoint'] ?? '');
        $this->usuario = (string) ($credentials['usuario'] ?? '');
        $this->clave = (string) ($credentials['clave'] ?? '');
        $this->tenant = (string) ($credentials['tenant'] ?? '');
    }

    /**
     * URL que usa SoapClient como WSDL. Los servicios WCF (.svc) exponen el WSDL en ?wsdl (a veces ?singleWsdl).
     */
    protected function wsdlLocation(): string
    {
        $url = trim($this->endpoint);
        if ($url === '') {
            return '';
        }
        if (preg_match('/[?&]wsdl\b/i', $url) || preg_match('/[?&]singleWsdl\b/i', $url)) {
            return $url;
        }
        if (preg_match('/\.svc\/?$/i', $url)) {
            $url = rtrim($url, '/');

            return $url.'?wsdl';
        }

        return $url;
    }

    public function enviarCFE(string $xmlCfe, string $referenciaErp): array
    {
        Log::info('Enviando XML a SICFE vía SoapClient');

        $client = new \SoapClient($this->wsdlLocation(), [
            'trace' => true,
            'exceptions' => true,
        ]);

        $params = [
            'nomusuario' => $this->usuario,
            'clave' => $this->clave,
            'tenant' => $this->tenant,
            'cliente' => '',
            'cfexml' => $xmlCfe,
            'referenciaERP' => $referenciaErp,
            'referenciaERP2' => '',
            'devolverQR' => true,
            'sizeQR' => 30,
            'imprime' => 0,
            'recurso' => 0,
            'template' => 0,
            'devolverXML' => true,
            'erpPideValidacion' => false,
            'version' => '',
        ];

        try {
            $response = $client->__soapCall('EnvioCFE', [$params]);
            return [
                'raw' => $client->__getLastResponse(),
                'parsed' => $response
            ];
        } catch (\SoapFault $e) {
            Log::error("Error SOAP al enviar CFE: {$e->getMessage()}");
            throw new \Exception('Fallo al enviar CFE a SICFE: ' . $e->getMessage());
        }
    }

    public function obtenerCfePdf(\App\Models\CFE $cfe): string
    {
        $params = [
            'nomusuario' => $this->usuario,
            'clave' => $this->clave,
            'p_tenant' => $this->tenant,
            'template' => null,
            'p_idCFE' => [
                'Numero' => $cfe->number,
                'Serie' => $cfe->serie,
                'Tipo' => $cfe->type,
                'observado' => false,
                'rucemisor' => $cfe->issuer_rut,
            ],
        ];

        try {
            Log::info('[SICFE] Enviando solicitud ObtenerPDF con:', $params);

            $client = new \SoapClient($this->wsdlLocation(), [
                'trace' => true,
                'exceptions' => true,
            ]);

            $result = $client->ObtenerPDF($params);
            $obtenerPDFResult = $result->ObtenerPDFResult ?? null;

            if (!$obtenerPDFResult || !isset($obtenerPDFResult->Buffer)) {
                Log::error('[SICFE] La respuesta no contiene un campo Buffer');
                throw new \Exception('La respuesta de SICFE no contiene un campo Buffer.');
            }

            $pdfBinary = $obtenerPDFResult->Buffer;

            if (!str_starts_with($pdfBinary, '%PDF')) {
                Log::error('[SICFE] Contenido no válido o corrupto. No empieza con %PDF');
                throw new \Exception('El contenido PDF es inválido o corrupto.');
            }

            return $obtenerPDFResult->Buffer;

        } catch (\Exception $e) {
            Log::error("[SICFE] Error al obtener PDF del CFE: " . $e->getMessage());
            throw new \Exception('Error al descargar el PDF desde SICFE. Detalle: ' . $e->getMessage());
        }
    }

    /**
     * Consulta el estado DGI de un CFE emitido.
     */
    public function obtenerEstadoCFE(array $idCfe): array
    {
        $params = [
            'pUsuario' => $this->usuario,
            'pClave'   => $this->clave,
            'pTenant'  => $this->tenant,
            'pIDCFE'   => [
                'Numero'    => $idCfe['Numero'],
                'Serie'     => $idCfe['Serie'],
                'Tipo'      => $idCfe['Tipo'],
                'observado' => $idCfe['observado'] ?? 1,
                'rucemisor' => $idCfe['rucemisor'],
            ],
        ];

        try {
            Log::info('[SICFE] ObtenerEstadoCFE params:', $params);

            $client = new \SoapClient($this->wsdlLocation(), [
                'trace' => true,
                'exceptions' => true,
            ]);

            $result = $client->ObtenerEstadoCFE($params);
            $parsed = $result->ObtenerEstadoCFEResult ?? null;

            $response = [
                'Estado'     => $parsed->Estado ?? '',
                'CodRechazo' => $parsed->CodRechazo ?? '',
                'MotRechazo' => $parsed->MotRechazo ?? '',
            ];

            Log::info('[SICFE] ObtenerEstadoCFE respuesta:', $response);

            return $response;
        } catch (\SoapFault $e) {
            Log::error('[SICFE] Error al obtener estado CFE: ' . $e->getMessage());
            throw new \Exception('Error al consultar estado del CFE en SICFE: ' . $e->getMessage());
        }
    }

    /**
     * Obtiene los CFEs recibidos (comprobantes de proveedores) con datos extendidos.
     */
    public function obtenerCFEsRecibidosExtendido(?string $fechaDesde = null, ?string $fechaHasta = null, string $estado = ''): array
    {
        $params = [
            'nomusuario'         => $this->usuario,
            'clave'              => $this->clave,
            'param_tenant'       => $this->tenant,
            'fecha_desde'        => $fechaDesde,
            'fecha_hasta'        => $fechaHasta,
            'estado'             => $estado,
            'devolverXML'        => false,
            'rucEmisor'          => '',
            'consideraCobranzas' => '',
        ];

        try {
            Log::info('[SICFE] ObtenerCFEsRecibidosExtendido params:', $params);

            $client = new \SoapClient($this->wsdlLocation(), [
                'trace' => true,
                'exceptions' => true,
            ]);

            $result = $client->ObtenerCFEsRecibidosExtendido($params);
            $parsed = $result->ObtenerCFEsRecibidosExtendidoResult ?? null;

            if (!$parsed || (isset($parsed->Codigo) && $parsed->Codigo != 0)) {
                $msg = $parsed->Descripcion ?? 'Error desconocido';
                Log::error('[SICFE] Error en ObtenerCFEsRecibidosExtendido: ' . $msg);
                return [];
            }

            $cfes = $parsed->CFEsRecibidos->CFERecibidoExtendidoDTO ?? [];

            // Si es un solo objeto, convertirlo a array
            if (!is_array($cfes)) {
                $cfes = [$cfes];
            }

            $formatted = json_decode(json_encode($cfes), true);

            Log::info('[SICFE] CFEs recibidos encontrados: ' . count($formatted));

            return $formatted;
        } catch (\SoapFault $e) {
            Log::error('[SICFE] Error al obtener CFEs recibidos: ' . $e->getMessage());
            throw new \Exception('Error al obtener CFEs recibidos de SICFE: ' . $e->getMessage());
        }
    }

    public function obtenerDatosRucDgi(array $params): array
    {
        try {
            Log::info('[SICFE] Enviando solicitud a ObtenerDatosRUCDGI con los siguientes parámetros:', $params);

            $client = new \SoapClient($this->wsdlLocation(), [
                'trace' => true,
                'exceptions' => true,
            ]);

            $response = $client->__soapCall('ObtenerDatosRUCDGI', [$params]);
            $formattedResponse = json_decode(json_encode($response), true);

            Log::info('[SICFE] Respuesta recibida de ObtenerDatosRUCDGI:', ['response' => $formattedResponse]);

            return $formattedResponse;
        } catch (\SoapFault $e) {
            Log::error('[SICFE] Error al consultar datos RUC DGI: ' . $e->getMessage(), [
                'params' => $params
            ]);
            throw new \Exception('Error al consultar datos RUC DGI: ' . $e->getMessage());
        }
    }
}
