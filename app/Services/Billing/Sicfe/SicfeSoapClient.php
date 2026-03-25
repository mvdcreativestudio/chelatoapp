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
