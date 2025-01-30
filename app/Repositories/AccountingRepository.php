<?php

namespace App\Repositories;

use App\Exceptions\CFEException;
use App\Http\Requests\EmitNoteRequest;
use App\Models\CFE;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Collection;
use Illuminate\Http\UploadedFile;
use App\Models\Order;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use App\Models\CurrencyRate;
use Illuminate\Support\Facades\Crypt;
use App\Models\Store;
use App\Models\Client;

class AccountingRepository
{
    /**
     * Realiza el login en el servicio externo y devuelve las cookies de la sesión.
     *
     * @param Store $store
     * @return array|null
    */
    public function login(Store $store): ?array
    {
        if (!$store || !$store->pymo_user || !$store->pymo_password) {
            Log::error('No se encontraron las credenciales de PyMo para la empresa del usuario.');
            return null;
        }

        try {
            Log::info('Contraseña encriptada: ' . $store->pymo_password);
            $decryptedPassword = Crypt::decryptString($store->pymo_password);
        } catch (\Exception $e) {
            Log::error('Error al desencriptar la contraseña de PyMo: ' . $e->getMessage());
            return null;
        }

        $loginResponse = Http::post(env('PYMO_HOST') . ':' . env('PYMO_PORT') . '/' . env('PYMO_VERSION') . '/login', [
            'email' => $store->pymo_user,
            'password' => $decryptedPassword,
        ]);

        if ($loginResponse->failed()) {
            return null;
        }

        $cookies = $loginResponse->cookies();
        $cookieJar = [];

        foreach ($cookies as $cookie) {
            $cookieJar[$cookie->getName()] = $cookie->getValue();
        }

        return $cookieJar;
    }


    /**
     * Obtiene todos los recibos con las relaciones necesarias.
     *
     * @return Collection
    */
    public function getInvoicesWithRelations(): Collection
    {
        $validTypes = [101, 102, 103, 111, 112, 113]; // Tipos válidos de CFE, eTickets, eFacturas, Notas de Crédito y Notas de Débito respectivamente

        if (auth()->user()->can('view_all_accounting')) {
            return CFE::with('order.client', 'order.store')
                ->orderBy('created_at', 'desc')
                ->whereIn('type', $validTypes)
                ->where('received', false)
                ->get();
        }

        return CFE::with('order.client', 'order.store')
            ->whereIn('type', $validTypes)
            ->orderBy('created_at', 'desc')
            ->whereHas('order.store', function ($query) {
                $query->where('id', auth()->user()->store_id);
            })
            ->where('received', false)
            ->get();
    }

    /**
     * Prepara los datos de los recibos para ser usados en DataTables.
     *
     * @return Collection
    */
    public function getInvoicesDataForDatatables(): Collection
    {
      $invoices = $this->getInvoicesWithRelations();

      return $invoices->map(function ($invoice) {
          $typeCFEs = [
            101 => 'eTicket',
            102 => 'eTicket - Nota de Crédito',
            103 => 'eTicket - Nota de Débito',
            111 => 'eFactura',
            112 => 'eFactura - Nota de Crédito',
            113 => 'eFactura - Nota de Débito',
          ];

          if ($invoice->is_receipt) {
              $typeCFEs[101] = 'eTicket - Recibo';
              $typeCFEs[111] = 'eFactura - Recibo';
          }

          if (
              !$invoice->is_receipt &&
              in_array($invoice->type, [101, 111]) &&
              $invoice->relatedCfes->count() > 0 &&
              $invoice->relatedCfes->contains(function ($relatedCfe) use ($invoice) {
                  return $relatedCfe->type == $invoice->type;
              })
          ) {
              $invoice->hide_emit = true;
          }

          return [
              'id' => $invoice->id,
              'store_name' => $invoice->order->store->name ?? 'N/A',
              'client_name' => $invoice->order->client->name ?? 'Consumidor Final',
              'client_email' => $invoice->order->client->email ?? '',
              'client_lastname' => $invoice->order->client->lastname ?? '',
              'date' => $invoice->emitionDate,
              'order_id' => $invoice->order->id,
              'type' => $typeCFEs[$invoice->type] ?? '',
              'currency' => 'UYU',
              'total' => $invoice->total,
              'qrUrl' => $invoice->qrUrl,
              'order_uuid' => $invoice->order->uuid,
              'serie' => $invoice->serie,
              'cfeId' => $invoice->cfeId,
              'nro' => $invoice->nro,
              'balance' => $invoice->balance,
              'caeNumber' => $invoice->caeNumber,
              'caeRange' => $invoice->caeRange,
              'caeExpirationDate' => $invoice->caeExpirationDate,
              'sentXmlHash' => $invoice->sentXmlHash,
              'securityCode' => $invoice->securityCode,
              'reason' => $invoice->reason,
              'associated_id' => $invoice->main_cfe_id,
              'is_receipt' => $invoice->is_receipt,
              'hide_emit' => $invoice->hide_emit,
              'status' => $invoice->status ?? '',
          ];
      });
    }

    /**
     * Datatable de los CFEs recibidos.
     *
     * @return Collection
    */
    public function getReceivedInvoicesDataForDatatables(): Collection
    {
      $invoices = $this->getReceivedInvoicesWithRelations();

      return $invoices->map(function ($invoice) {
          $typeCFEs = [
            101 => 'eTicket',
            102 => 'eTicket - Nota de Crédito',
            103 => 'eTicket - Nota de Débito',
            111 => 'eFactura',
            112 => 'eFactura - Nota de Crédito',
            113 => 'eFactura - Nota de Débito',
          ];

          if ($invoice->is_receipt) {
              $typeCFEs[101] = 'eTicket - Recibo';
              $typeCFEs[111] = 'eFactura - Recibo';
          }

          if (
              !$invoice->is_receipt &&
              in_array($invoice->type, [101, 111]) &&
              $invoice->relatedCfes->count() > 0 &&
              $invoice->relatedCfes->contains(function ($relatedCfe) use ($invoice) {
                  return $relatedCfe->type == $invoice->type;
              })
          ) {
              $invoice->hide_emit = true;
          }

          return [
              'id' => $invoice->id,
              'store_name' => $invoice->order->store->name ?? 'N/A',
              'client_name' => $invoice->order->client->name ?? 'Consumidor Final',
              'client_email' => $invoice->order->client->email ?? '',
              'client_lastname' => $invoice->order->client->lastname ?? '',
              'date' => $invoice->emitionDate,
              'order_id' => $invoice->order->id,
              'type' => $typeCFEs[$invoice->type] ?? '',
              'currency' => 'UYU',
              'total' => $invoice->total,
              'qrUrl' => $invoice->qrUrl,
              'order_uuid' => $invoice->order->uuid,
              'serie' => $invoice->serie,
              'cfeId' => $invoice->cfeId,
              'nro' => $invoice->nro,
              'balance' => $invoice->balance,
              'caeNumber' => $invoice->caeNumber,
              'caeRange' => $invoice->caeRange,
              'caeExpirationDate' => $invoice->caeExpirationDate,
              'sentXmlHash' => $invoice->sentXmlHash,
              'securityCode' => $invoice->securityCode,
              'reason' => $invoice->reason,
              'associated_id' => $invoice->main_cfe_id,
              'is_receipt' => $invoice->is_receipt,
              'hide_emit' => $invoice->hide_emit,
              'status' => $invoice->status ?? ''
          ];
      });
    }

    /**
     * Obtiene los comprobantes fiscales electrónicos (CFE) enviados de una empresa.
     *
     * @param string $rut
     * @param array $cookies
     * @return array|null
    */
    public function getCompanySentCfes(string $rut, array $cookies): ?array
    {
      $response = Http::withCookies($cookies, parse_url(env('PYMO_HOST'), PHP_URL_HOST))
          ->get(env('PYMO_HOST') . ':' . env('PYMO_PORT') . '/' . env('PYMO_VERSION') . '/companies/' . $rut . '/sentCfes');

      if ($response->failed() || !isset($response->json()['payload']['companySentCfes'])) {
          return null;
      }

      return $response->json()['payload']['companySentCfes'];
    }

    /**
     * Sube el logo de la empresa.
     *
     * @param int $storeId
     * @param UploadedFile $logo
     * @return bool
    */
    public function uploadCompanyLogo(string $storeId, UploadedFile $logo): bool
    {
      $store = Store::find($storeId);

      $cookies = $this->login($store);

      if (!$cookies) {
          return false;
      }

      $rut = $store->rut;

      Log::info('Subiendo logo de la empresa:', ['store' => $store->name]);

      $logoResponse = Http::withCookies($cookies, parse_url(env('PYMO_HOST'), PHP_URL_HOST))
          ->attach('logo', $logo->get(), 'logo.jpg')
          ->post(env('PYMO_HOST') . ':' . env('PYMO_PORT') . '/' . env('PYMO_VERSION') . '/companies/' . $rut . '/logo');

      Log::info('Respuesta del logo:', ['response' => $logoResponse->body()]);

      return $logoResponse->successful();
    }

    /**
     * Obtiene el logo de la empresa y lo guarda localmente.
     *
     * @param Store $store
     * @return string|null
    */
    public function getCompanyLogo(string $storeId)
    {
        $store = Store::find($storeId);
        $cookies = $this->login($store);

        if (!$cookies) {
            return null;
        }

        $rut = $store->rut;

        $response = Http::withCookies($cookies, parse_url(env('PYMO_HOST'), PHP_URL_HOST))
            ->get(env('PYMO_HOST') . ':' . env('PYMO_PORT') . '/' . env('PYMO_VERSION') . "/companies/{$rut}/logo");

        if ($response->successful()) {
            return 'data:image/jpeg;base64,' . base64_encode($response->body());
        }

        return null;
    }

    /**
     * Guarda la imagen del logo en almacenamiento local.
     *
     * @param string $imageContent
     * @return string
    */
    private function saveLogoLocally(string $imageContent): string
    {
      $logoPath = 'public/assets/img/logos/company_logo.jpg';
      Storage::put($logoPath, $imageContent);

      return Storage::url($logoPath);
    }

    /**
     * Obtiene la información de la empresa.
     *
     * @param Store $store
     * @return array|null
    */
    public function getCompanyInfo(Store $store): ?array
    {
        $cookies = $this->login($store);

        if (!$cookies) {
            Log::error('No se pudo iniciar sesión para obtener la información de la empresa.');
            return null;
        }

        if (!$store->rut) {
            Log::error('No se encontró el RUT de la empresa para obtener la información de la empresa.');
            return null;
        }

        // Construir la URL para obtener la información de la empresa
        $url = env('PYMO_HOST') . ':' . env('PYMO_PORT') . '/' . env('PYMO_VERSION') . '/companies/' . $store->rut;

        try {
            $companyResponse = Http::withCookies($cookies, parse_url(env('PYMO_HOST'), PHP_URL_HOST))
                ->get($url);

            if ($companyResponse->failed() || !isset($companyResponse->json()['payload']['company'])) {
                Log::error('Error al obtener la información de la empresa: ' . $companyResponse->body());
                return null;
            }

            return $companyResponse->json()['payload']['company'];
        } catch (\Exception $e) {
            Log::error('Excepción al obtener la información de la empresa: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Emite un CFE (eFactura o eTicket) para una orden.
     *
     * @param Order $order
     * @param float|null $amountToBill
     * @param int|null $payType
     * @param string|null $adenda
     * @param string|null $emissionDate
     * @return void
    */
    public function emitCFE(Order $order, ?float $amountToBill = null, ?int $payType = 1, ?string $adenda = null, ?string $emissionDate = null): void
    {
        $store = $order->store;

        $cookies = $this->login($store);

        if (!$cookies) {
            Log::error('No se pudo iniciar sesión para emitir el CFE.');
            return;
        }


        if ($order->client_id) {
          $client = $order->client;
          if (!$this->validateClientData($client)) {
              throw new \Exception('El cliente no tiene todos los datos requeridos para emitir el CFE.');
          }
      }

        $rut = $store->rut;

        $branchOffice = $store->pymo_branch_office;

        if (!$store || !$store->rut) {
            Log::error('No se encontró el RUT de la empresa para emitir el CFE.');
            return;
        }

        if (!$branchOffice) {
            Log::error('No se encontró la sucursal de la empresa para emitir el CFE.');
            return;
        }

        if ($rut) {
            // Obtener el cliente asociado a la orden
            $client = $order->client;

            // Determinar el tipo de documento
            $cfeType = '101'; // Por defecto, es eTicket
            if ($client) {
                // Si hay cliente, verificar su tipo
                $cfeType = $client->type === 'company' ? '111' : '101'; // '111' para empresa, '101' para individuo
            }

            Log::info('Tipo del CFE:', ['cfeType' => $cfeType]);

            $url = env('PYMO_HOST') . ':' . env('PYMO_PORT') . '/' . env('PYMO_VERSION') . '/companies/' . $rut . '/sendCfes/' . $branchOffice;

            $amountToBill = $amountToBill ?? $order->total;

            $hasSpecialCaes = $store->has_special_caes;

            $cfeData = $this->prepareCFEData($order, $cfeType, $amountToBill, $payType, $hasSpecialCaes, $adenda, $emissionDate);

            Log::info('Datos del CFE:', $cfeData);

            try {
                $payloadArray = [
                    'emailsToNotify' => [],
                    $cfeType => [$cfeData],
                ];

                $payload = (object)$payloadArray;

                $response = Http::withCookies($cookies, parse_url(env('PYMO_HOST'), PHP_URL_HOST))
                    ->asJson()
                    ->post($url, $payload);

                if ($response->successful()) {
                    Log::info('CFE emitido correctamente: ' . $response->body());

                    $responseData = $response->json();

                    foreach ($responseData['payload']['cfesIds'] as $cfe) {
                        try {
                            $invoice = CFE::create([
                                'order_id' => $order->id,
                                'store_id' => $order->store_id,
                                'type' => $cfeType,
                                'serie' => $cfe['serie'],
                                'nro' => $cfe['nro'],
                                'caeNumber' => $cfe['caeNumber'],
                                'caeRange' => json_encode($cfe['caeRange']),
                                'caeExpirationDate' => $cfe['caeExpirationDate'],
                                'total' => $amountToBill,
                                'balance' => $amountToBill,
                                'emitionDate' => $cfe['emitionDate'],
                                'sentXmlHash' => $cfe['sentXmlHash'],
                                'securityCode' => $cfe['securityCode'],
                                'qrUrl' => $cfe['qrUrl'],
                                'cfeId' => $cfe['id'],
                                'status' => $cfeType === '101' ? 'SCHEDULED_WITHOUT_CAE_NRO' : 'CREATED_WITHOUT_CAE_NRO',
                            ]);

                            Log::info('Receipt creado correctamente:', $invoice->toArray());
                        } catch (\Exception $e) {
                            throw new \Exception('Error al crear el recibo: ' . $e->getMessage());
                        }
                    }
                } else {
                    Log::error('Error al emitir CFE: ' . $response->body());
                }
            } catch (\Exception $e) {
                throw new \Exception('Error al emitir CFE: ' . $e->getMessage());
            }
        } else {
            Log::error('No se encontró el RUT de la empresa para emitir el CFE.');
        }
    }

    /**
     * Obtiene los campos faltantes en el cliente para emitir el CFE.
     *
     * @param Client $client
     * @return array
    */
    private function getMissingFields($client): array
    {
        $missingFields = [];

        if ($client->type === 'individual') {
            $requiredFields = [
                'name' => 'Nombre',
                'lastname' => 'Apellido',
                'address' => 'Dirección',
                'city' => 'Ciudad',
                'state' => 'Departamento',
                'country' => 'País'
            ];

            if (empty($client->ci) && empty($client->passport) && empty($client->other_id_type)) {
              $missingFields[] = 'Documento de identidad (CI, Pasaporte u Otro)';
            }
        } else {
            $requiredFields = [
                'company_name' => 'Razón Social',
                'rut' => 'RUT',
                'address' => 'Dirección',
                'city' => 'Ciudad',
                'state' => 'Departamento',
                'country' => 'País'
            ];
        }

        foreach ($requiredFields as $field => $label) {
            if (empty($client->$field)) {
                $missingFields[] = $label;
            }
        }

        return $missingFields;
    }

    /**
     * Valida los datos del cliente antes de emitir el CFE.
     *
     * @param Client $client
     * @return bool
     */
    private function validateClientData($client): bool
    {
        if (!$client) {
            throw new \Exception('Cliente no encontrado');
        }

        $missingFields = $this->getMissingFields($client);

        if (!empty($missingFields)) {
            $clientType = $client->type === 'individual' ? 'individuo' : 'empresa';
            throw new \Exception(
                "El cliente no tiene los siguientes campos requeridos para facturar: " .
                    implode(', ', $missingFields)
            );
        }

        return true;
    }

    /**
     * Prepara los datos necesarios para emitir el CFE.
     *
     * @param Order $order
     * @param string $cfeType
     * @param float $amountToBill
     * @param int $payType
     * @param bool $hasSpecialCaes
     * @param string|null $adenda
     * @param string|null $emissionDate
     * @return array
    */
    private function prepareCFEData(Order $order, string $cfeType, float $amountToBill, int $payType, bool $hasSpecialCaes, ?string $adenda, ?string $emissionDate): array
    {
        $client = $order->client;
        $products = is_string($order->products) ? json_decode($order->products, true) : $order->products;
        $proportion = ($amountToBill < $order->total) ? $amountToBill / $order->total : 1;

        // Mapear tasas de IVA
        $taxRateMap = [
            1 => 1, // Exento
            2 => 2, // Tasa mínima (10%)
            3 => 3, // Tasa básica (22%)
        ];

        // Si el cliente es exento de IVA, todos los productos serán exentos (IndFact = 1)
        $isClientExempt = $client && $client->tax_rate_id == 1;

        // Calcular descuento proporcional
        $totalDiscount = $order->discount ?? 0;

        // Evitar dividir por 0
        $orderSubtotal = $order->subtotal > 0 ? $order->subtotal : 1;

        $subtotalConIVA = 0;
        $totalDescuento = 0;

        $items = array_map(function ($product, $index) use ($proportion, $isClientExempt, $totalDiscount, $orderSubtotal, &$subtotalConIVA, &$totalDescuento, $taxRateMap, $hasSpecialCaes) {
            $adjustedAmount = round($product['quantity'] * $proportion, 2);

            // Si el cliente es exento, forzar IndFact = 1
            if ($isClientExempt) {
                $indFact = 1;
            } else {
                $taxRateId = $product['tax_rate_id'] ?? 3; // Default a Tasa Básica (22%)
                $indFact = $hasSpecialCaes ? 16 : ($taxRateMap[$taxRateId] ?? 3);
            }

            $productPriceConIVA = round($product['price'], 2);

            // Calcular el descuento proporcional del producto
            $discountAmount = round(($totalDiscount * ($productPriceConIVA / $orderSubtotal)), 2);

            $totalDescuento += $discountAmount * $adjustedAmount;
            $subtotalConIVA += ($productPriceConIVA - $discountAmount) * $adjustedAmount;

              $cleanedProductName = $this->cleanProductName($product['name']);

              Log::info('Descuentos:', ['PrecioConIVA' => $productPriceConIVA, 'Descuento %' => $discountPercentage, 'Descuento $' => $discountAmount]);

              return [
                  'NroLinDet' => $index + 1,
                  'IndFact' => $indFact,
                  'IndFact' => $indFact,
                  'NomItem' => $cleanedProductName,
                  'Cantidad' => $adjustedAmount,
                  'UniMed' => 'N/A',
                  'DescuentoPct' => round($discountPercentage, 2),
                  'DescuentoMonto' => $discountAmount,
                  'MontoItem' => round(($productPriceConIVA - $discountAmount) * $adjustedAmount, 2),
                  'PrecioUnitario' => $productPriceConIVA,
              ];
          }, $products, array_keys($products));
        }
            return [
                'NroLinDet' => $index + 1,
                'IndFact' => $indFact,
                'NomItem' => $this->cleanProductName($product['name']),
                'Cantidad' => $adjustedAmount,
                'UniMed' => 'N/A',
                'DescuentoPct' => round(($discountAmount / $productPriceConIVA) * 100, 2),
                'DescuentoMonto' => $discountAmount,
                'MontoItem' => round(($productPriceConIVA - $discountAmount) * $adjustedAmount, 2),
                'PrecioUnitario' => $productPriceConIVA,
            ];
        }, $products, array_keys($products));

        // Redondeo final del subtotal
>>>>>>> ad2d3d90 (Configuración de Stores para seleccionar Régimen Impositivo)
        $subtotalConIVA = round($subtotalConIVA, 2);

        // Construcción del CFE
        $cfeData = [
            'clientEmissionId' => $order->uuid . now()->timestamp,
            'adenda' => $adenda ? $adenda : 'Orden ' . $order->id . ' - Sumeria.',
            'IdDoc' => [
                'MntBruto' => $hasSpecialCaes ? 3 : 1,
                'FmaPago' => $payType,
                'FchEmis' => $emissionDate ? $emissionDate : now()->toIso8601String(),
            ],
            'Receptor' => (object) [],
            'Totales' => [
                'TpoMoneda' => 'UYU',
            ],
            'Items' => $items,
        ];

        // Agregar el CAEEspecial si se usa
        if ($hasSpecialCaes) {
            $cfeData['IdDoc']['CAEEspecial'] = 2;
        }

        // Datos del cliente
        if ($client) {
            $tipoDocRecep = match (true) {
                $client->type === 'company' && $client->rut => 2, // RUC
                $client->type === 'individual' && $client->ci => 3, // CI
                $client->type === 'individual' && $client->passport => 5, // Pasaporte
                $client->type === 'individual' && $client->other_id_type => 4, // Otros
                default => null
            };

            $docRecep = $client->rut ?? $client->ci ?? null;
            $docRecepExt = $client->passport ?? $client->other_id_type ?? null;

            $cfeData['Receptor'] = [
                'TipoDocRecep' => $tipoDocRecep,
                'DocRecep' => $docRecep,
                'DocRecepExt' => $docRecepExt,
                'CodPaisRecep' => 'UY',
                'RznSocRecep' => $client->type === 'company' ? $client->company_name : $client->name . ' ' . $client->lastname,
                'DirRecep' => $client->address,
                'CiudadRecep' => $client->city,
                'DeptoRecep' => $client->state,
            ];
        }

        return $cfeData;
    }




    /**
     * Limpia el nombre del producto y lo limita a 50 caracteres.
     *
     * @param string $productName
     * @return string
     */
    private function cleanProductName(string $productName): string
    {
        // Eliminar caracteres especiales no deseados
        $cleanedName = preg_replace('/[^A-Za-z0-9\s\-.,]/', '', $productName);

        // Limitar el nombre a 80 caracteres
        return mb_strimwidth($cleanedName, 0, 80);
    }


    /**
     * Obtiene estadísticas para el dashboard contable.
     *
     * @return array
    */
    public function getDashboardStatistics(): array
    {
      $invoices = $this->getInvoicesWithRelations();
      $totalReceipts = $invoices->count();
      $totalIncome = $invoices->sum('balance');
      $storeWithMostReceipts = $invoices->groupBy('store_id')
          ->sortByDesc(function ($group) {
              return $group->count();
          })->first();
      $storeNameWithMostReceipts = $storeWithMostReceipts ? $storeWithMostReceipts->first()->order->store->name : 'N/A';

      return compact('invoices', 'totalReceipts', 'totalIncome', 'storeNameWithMostReceipts');
    }


    /**
     * Emite una nota de crédito o débito para una factura o eTicket existente.
     *
     * @param int $invoiceId
     * @param EmitNoteRequest $request
     * @return void
     * @throws Exception
    */
    public function emitNote(int $invoiceId, EmitNoteRequest $request): void
    {
        $invoice = CFE::findOrFail($invoiceId);
        $store = $invoice->order->store;

        $cookies = $this->login($store);

        if (!$cookies) {
            throw new \Exception('No se pudo iniciar sesión para emitir la nota.');
        }

        $rut = $store->rut;
        $branchOffice = $store->pymo_branch_office;

        if (!$store || !$rut) {
            throw new \Exception('No se encontró el RUT de la empresa para emitir la nota.');
        }

        if (!$branchOffice) {
            throw new \Exception('No se encontró la sucursal de la empresa para emitir la nota.');
        }

        // Validar que el tipo sea eFactura (111) o eTicket (101)
        if (!in_array($invoice->type, [101, 111])) {
            throw new \Exception('No se puede emitir una nota sobre este tipo de documento.');
        }

        $noteType = $request->noteType;
        $noteAmount = $request->noteAmount;
        $reason = $request->reason;
        $emissionDate = $request->emissionDate;

        // Validar tipo de documento para eFactura (111)
        if ($invoice->type == 111) {
            $orderDocType = $invoice->order->doc_type;
            $orderDocument = $invoice->order->document;

            if ($orderDocType == 2 && strlen($orderDocument) !== 12) { // RUC
                throw new \Exception('El RUC debe tener 12 caracteres.');
            } elseif ($orderDocType == 3 && strlen($orderDocument) !== 8) { // CI
                throw new \Exception('La CI debe tener 8 caracteres.');
            }
        }

        $cfeType = match ($invoice->type) {
            101 => $noteType === 'credit' ? '102' : '103',
            111 => $noteType === 'credit' ? '112' : '113',
            default => throw new \Exception('Tipo de CFE no soportado para notas.')
        };

        // Validar el balance del CFE principal
        $currentBalance = $invoice->balance ?? 0;

        if ($noteType === 'credit' && $noteAmount > $currentBalance) {
            throw new \Exception('El monto de la nota de crédito no puede ser mayor que el balance actual.');
        }

        // Calcular el nuevo balance
        $newBalance = ($noteType === 'credit') ? $currentBalance - $noteAmount : $currentBalance + $noteAmount;

        if ($newBalance < 0) {
            throw new \Exception('El balance no puede ser negativo.');
        }

        // Emitir la nota y preparar los datos
        $notaData = $this->prepareNoteData($invoice, $noteAmount, $reason, $noteType, $emissionDate);

        $url = env('PYMO_HOST') . ':' . env('PYMO_PORT') . '/' . env('PYMO_VERSION') . '/companies/' . $rut . '/sendCfes/' . $branchOffice;

        try {
            $payloadArray = [
                'emailsToNotify' => [],
                $cfeType => [$notaData],
            ];

            $response = Http::withCookies($cookies, parse_url(env('PYMO_HOST'), PHP_URL_HOST))
                ->asJson()
                ->post($url, (object)$payloadArray);

            if ($response->successful()) {
                Log::info('Nota emitida correctamente: ' . $response->body());

                $responseData = $response->json();
                foreach ($responseData['payload']['cfesIds'] as $cfe) {
                    // Crear el nuevo CFE (Nota)
                    $newCfe = CFE::create([
                        'order_id' => $invoice->order_id,
                        'store_id' => $invoice->store_id,
                        'type' => $cfeType,
                        'serie' => $cfe['serie'],
                        'nro' => $cfe['nro'],
                        'caeNumber' => $cfe['caeNumber'],
                        'caeRange' => json_encode($cfe['caeRange']),
                        'caeExpirationDate' => $cfe['caeExpirationDate'],
                        'total' => $noteAmount,
                        'emitionDate' => $cfe['emitionDate'],
                        'sentXmlHash' => $cfe['sentXmlHash'],
                        'securityCode' => $cfe['securityCode'],
                        'qrUrl' => $cfe['qrUrl'],
                        'cfeId' => $cfe['id'],
                        'reason' => $reason,
                        'main_cfe_id' => $invoice->id,
                        'status' => 'CREATED_WITHOUT_CAE_NRO',
                    ]);

                    // Actualizar el balance del CFE principal
                    $invoice->balance = $newBalance;
                    $invoice->save();
                }
            } else {
                throw new \Exception('Error al emitir nota: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('Excepción al emitir nota: ' . $e->getMessage());
            throw new \Exception('Error al emitir nota: ' . $e->getMessage());
        }
    }

    /**
     * Prepara los datos necesarios para emitir una nota de crédito o débito.
     *
     * @param CFE $invoice
     * @param float $noteAmount
     * @param string $reason
     * @param string $noteType
     * @param string $emissionDate
     * @return array
    */
    private function prepareNoteData(CFE $invoice, float $noteAmount, string $reason, string $noteType, string $emissionDate = null): array
    {
        $order = $invoice->order;

        // $usdRate = CurrencyRate::where('name', 'Dólar')
        //     ->first()
        //     ->histories()
        //     ->orderByRaw('ABS(TIMESTAMPDIFF(SECOND, date, ?))', [$order->created_at])
        //     ->first();

        // if ($usdRate) {
        //     $exchangeRate = (float) $usdRate->sell;
        // } else {
        //     throw new \Exception('No se encontró el tipo de cambio para el dólar.');
        // }

        Log::info('Tipo de nota: ' . $noteType);

        // El clientEmissionid debe ser unico sabiendo q puedo generar mas de un tipo de nota para una misma factura
        $notaData = [
          'clientEmissionId' => $order->uuid . '-' . $noteType . '-' . now()->timestamp,
          'adenda' => $reason,
          'IdDoc' => [
              'FchEmis' => $emissionDate ? $emissionDate : now()->toIso8601String(),
              'FmaPago' => '1',
          ],
          'Receptor' => (object) [], // Inicializar como objeto vacío
          'Totales' => [
              'TpoMoneda' => 'UYU',
              //'TpoCambio' => $exchangeRate,
          ],
          'Referencia' => [
              [
                  'NroLinRef' => '1',
                  'IndGlobal' => '1',
                  'TpoDocRef' => $invoice->type,
                  'Serie' => $invoice->serie,
                  'NroCFERef' => $invoice->nro,
                  'RazonRef' => $reason,
                  'FechaCFEref' => $invoice->emitionDate->toIso8601String()
              ]
          ],
          'Items' => [
              [
                  'NroLinDet' => '1',
                  'IndFact' => 6,
                  'NomItem' => 'Nota de ' . ($noteType == 'credit' ? 'Crédito' : 'Débito') . ' - Ajuste',
                  'Cantidad' => '1',
                  'UniMed' => 'N/A',
                  'PrecioUnitario' => $noteAmount,
                  'MontoItem' => $noteAmount,
              ]
          ],
          'Emisor' => [
              'GiroEmis' => 'Chelato'
          ]
        ];

        // Comprobar si existe un cliente y no es de tipo 'no-client'
        if ($order->client && $order->client->type !== 'no-client') {
          $tipoDocRecep = null;
          if ($order->client->type === 'company' && $order->client->rut) {
              $tipoDocRecep = 2; // RUC
          } elseif ($order->client->type === 'individual' && $order->client->ci) {
              $tipoDocRecep = 3; // CI
          } elseif ($order->client->type === 'individual' && $order->client->passport) {
              $tipoDocRecep = 5; // Pasaporte
          } elseif ($order->client->type === 'individual' && $order->client->other_id_type) {
              $tipoDocRecep = 4; // Otros
          }

          $docRecep = null;

          if ($order->client->rut) {
              $docRecep = $order->client->rut;
          } elseif ($order->client->ci) {
              $docRecep = $order->client->ci;
          }

          $docRecepExt = null;

          if ($order->client->passport) {
              $docRecepExt = $order->client->passport;
          } elseif ($order->client->other_id_type) {
              $docRecepExt = $order->client->other_id_type;
          }

          $notaData['Receptor'] = [
              'TipoDocRecep' => $tipoDocRecep,
              'DocRecep' => $docRecep,
              'DocRecepExt' => $docRecepExt,
              'CodPaisRecep' => 'UY',
              'RznSocRecep' => $order->client->type === 'company' ? $order->client->company_name : $order->client->name . ' ' . $order->client->lastname,
              'DirRecep' => $order->client->address,
              'CiudadRecep' => $order->client->city,
              'DeptoRecep' => $order->client->state,
              'CompraID' => $order->id,
          ];
        }



        if ($invoice->type == 111) {
            $notaData['IdDoc'] = array_merge($notaData['IdDoc'], [
                'ViaTransp' => '8',
                'ClauVenta' => 'N/A',
                'ModVenta' => '90'
            ]);
        }

        return $notaData;
    }

    /**
     * Obtiene el PDF de un CFE (eFactura o eTicket) para una orden específica.
     *
     * @param int $cfeId
     * @return Response
     * @throws Exception
    */
    public function getCfePdf(int $cfeId): Response
    {
        $cfe = CFE::findOrFail($cfeId);
        $store = $cfe->order->store;
        $rut = $store->rut;
        $branchOffice = $store->pymo_branch_office;

        $cookies = $this->login($store);

        if (!$cookies) {
            Log::error('No se pudo iniciar sesión para obtener el PDF del CFE.');
            throw new \Exception('No se pudo iniciar sesión para obtener el PDF del CFE.');
        }

        if (!$store || !$rut) {
            Log::error('No se encontró el RUT de la empresa para obtener el PDF del CFE.');
            throw new \Exception('No se encontró el RUT de la empresa para obtener el PDF del CFE.');
        }

        if (!$branchOffice) {
            Log::error('No se encontró la sucursal de la empresa para obtener el PDF del CFE.');
            throw new \Exception('No se encontró la sucursal de la empresa para obtener el PDF del CFE.');
        }

        // Construir la URL para obtener el PDF
        $cfeId = $cfe->cfeId;
        $url = env('PYMO_HOST') . ':' . env('PYMO_PORT') . '/' . env('PYMO_VERSION') . '/companies/' . $rut . '/invoices/?id=' . $cfeId;

        try {
            // Hacer la solicitud para obtener el PDF
            $response = Http::withCookies($cookies, parse_url(env('PYMO_HOST'), PHP_URL_HOST))
                ->asJson()
                ->get($url);

            if ($response->successful()) {
                $pdfContent = $response->body();

                // Enviar el PDF al navegador
                return response($pdfContent)
                    ->header('Content-Type', 'application/pdf')
                    ->header('Content-Disposition', 'attachment; filename="CFE_' . $cfeId . '.pdf"');
            } else {
                Log::error('Error al obtener el PDF del CFE: ' . $response->body());
                throw new \Exception('Error al obtener el PDF del CFE.');
            }
        } catch (\Exception $e) {
            Log::error('Excepción al obtener el PDF del CFE: ' . $e->getMessage());
            throw new \Exception('Error al obtener el PDF del CFE: ' . $e->getMessage());
        }
    }

    /**
     * Obtiene los documentos fiscales recibidos de una empresa.
     *
     * @param string $rut
     * @param array $cookies
     * @return array|null
    */
    public function fetchReceivedCfes(string $rut, array $cookies): ?array
    {
        $url = env('PYMO_HOST') . ':' . env('PYMO_PORT') . '/' . env('PYMO_VERSION') . '/companies/' . $rut . '/inSobres/cfes?l=10000';

        try {
            $response = Http::withCookies($cookies, parse_url(env('PYMO_HOST'), PHP_URL_HOST))
                ->get($url);

            if ($response->failed() || !isset($response->json()['payload']['receivedCfe'])) {
                Log::error('Error al obtener los recibos recibidos: ' . $response->body());
                return null;
            }

            return $response->json()['payload']['receivedCfe'];
        } catch (\Exception $e) {
            Log::error('Excepción al obtener los recibos recibidos: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Emite un recibo (cobranza) sobre una factura o eTicket existente.
     *
     * @param int $invoiceId
     * @param string|null $emissionDate
     * @return void
     * @throws \Exception
    */
    public function emitReceipt(int $invoiceId, ?string $emissionDate): void
    {
        $invoice = CFE::findOrFail($invoiceId);
        $store = $invoice->order->store;

        $cookies = $this->login($store);

        if (!$cookies) {
            throw new \Exception('No se pudo iniciar sesión para emitir el recibo.');
        }

        $rut = $store->rut;
        $branchOffice = $store->pymo_branch_office;

        if (!$store || !$rut) {
            throw new \Exception('No se encontró el RUT de la empresa para emitir el recibo.');
        }

        if (!$branchOffice) {
            throw new \Exception('No se encontró la sucursal de la empresa para emitir el recibo.');
        }

        // Preparar los datos del recibo
        $receiptData = $this->prepareReceiptData($invoice, $emissionDate);

        $url = env('PYMO_HOST') . ':' . env('PYMO_PORT') . '/' . env('PYMO_VERSION') . '/companies/' . $rut . '/sendCfes/' . $branchOffice;

        try {
            $payloadArray = [
                'emailsToNotify' => [],
                $invoice->type => [$receiptData],
            ];

            $response = Http::withCookies($cookies, parse_url(env('PYMO_HOST'), PHP_URL_HOST))
                ->asJson()
                ->post($url, (object) $payloadArray);

            if ($response->successful()) {
                Log::info('Recibo emitido correctamente: ' . $response->body());

                $responseData = $response->json();
                foreach ($responseData['payload']['cfesIds'] as $cfe) {
                    // Crear el nuevo CFE (Recibo)
                    $newCfe = CFE::create([
                        'order_id' => $invoice->order_id,
                        'store_id' => $invoice->store_id,
                        'type' => $invoice->type,
                        'serie' => $cfe['serie'],
                        'nro' => $cfe['nro'],
                        'caeNumber' => $cfe['caeNumber'],
                        'caeRange' => json_encode($cfe['caeRange']),
                        'caeExpirationDate' => $cfe['caeExpirationDate'],
                        'total' => $invoice->balance,
                        'emitionDate' => $cfe['emitionDate'],
                        'sentXmlHash' => $cfe['sentXmlHash'],
                        'securityCode' => $cfe['securityCode'],
                        'qrUrl' => $cfe['qrUrl'],
                        'cfeId' => $cfe['id'],
                        'reason' => 'Recibo de Cobranza',
                        'main_cfe_id' => $invoice->id,
                        'is_receipt' => true,
                        'status' => $invoice->type === '101' ? 'SCHEDULED_WITHOUT_CAE_NRO' : 'CREATED_WITHOUT_CAE_NRO',
                    ]);
                }
            } else {
                throw new \Exception('Error al emitir el recibo: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('Excepción al emitir recibo: ' . $e->getMessage());
            throw new \Exception('Error al emitir recibo: ' . $e->getMessage());
        }
    }

    /**
     * Prepara los datos necesarios para emitir un recibo (cobranza).
     *
     * @param CFE $invoice
     * @param string|null $emissionDate
     * @return array
    */
    private function prepareReceiptData(CFE $invoice, ?string $emissionDate): array
    {
        $order = $invoice->order;

        // Obtener la tasa de cambio del historial de CurrencyRate
        // $usdRate = CurrencyRate::where('name', 'Dólar')
        //     ->first()
        //     ->histories()
        //     ->orderByRaw('ABS(TIMESTAMPDIFF(SECOND, date, ?))', [$order->created_at])
        //     ->first();

        // if ($usdRate) {
        //     $exchangeRate = (float) $usdRate->sell;
        // } else {
        //     throw new \Exception('No se encontró el tipo de cambio para el dólar.');
        // }

        Log::info('Fecha de emisión del recibo: ' . $emissionDate);

        $data = [
            'clientEmissionId' => $invoice->order->uuid . '-R',
            'adenda' => 'Recibo de Cobranza sobre ' . ($invoice->type == 111 ? 'eFactura' : 'eTicket'),
            'IdDoc' => [
                'IndCobPropia' => '1',
                'FmaPago' => '1',
                'FchEmis' => $emissionDate ? $emissionDate : now()->toIso8601String(),
            ],
            'Receptor' => (object) [], // Inicializar como objeto vacío
            'Totales' => [
                'TpoMoneda' => 'UYU',
                // 'TpoCambio' => $exchangeRate, // Tasa de cambio en USD
            ],
            'Referencia' => [
                [
                    'NroLinRef' => 1,
                    'TpoDocRef' => $invoice->type,
                    'Serie' => $invoice->serie,
                    'NroCFERef' => $invoice->nro,
                    'FechaCFEref' => $invoice->emitionDate->toIso8601String(),
                ]
            ],
            'Items' => [
                [
                    'NroLinDet' => 1,
                    'IndFact' => '6',
                    'NomItem' => 'Cobranza sobre ' . ($invoice->type == 111 ? 'eFactura' : 'eTicket'),
                    'Cantidad' => '1',
                    'UniMed' => 'N/A',
                    'PrecioUnitario' => $invoice->balance,
                    'MontoItem' => $invoice->balance,
                ]
            ]
        ];

        // Comprobar si existe un cliente y no es de tipo 'no-client'
        if ($invoice->order->client && $invoice->order->client->type !== 'no-client') {
          $tipoDocRecep = null;
          if ($invoice->order->client->type === 'company' && $invoice->order->client->rut) {
              $tipoDocRecep = 2; // RUC
          } elseif ($invoice->order->client->type === 'individual' && $invoice->order->client->ci) {
              $tipoDocRecep = 3; // CI
          } elseif ($invoice->order->client->type === 'individual' && $invoice->order->client->passport) {
              $tipoDocRecep = 5; // Pasaporte
          } elseif ($invoice->order->client->type === 'individual' && $invoice->order->client->other_id_type) {
              $tipoDocRecep = 4; // Otros
          }

          $docRecep = null;
          if ($invoice->order->client->rut) {
              $docRecep = $invoice->order->client->rut;
          } elseif ($invoice->order->client->ci) {
              $docRecep = $invoice->order->client->ci;
          }

          $docRecepExt = null;

          if ($invoice->order->client->passport) {
              $docRecepExt = $invoice->order->client->passport;
          } elseif ($invoice->order->client->other_id_type) {
              $docRecepExt = $invoice->order->client->other_id_type;
          }

          $data['Receptor'] = [
              'TipoDocRecep' => $tipoDocRecep,
              'DocRecep' => $docRecep,
              'DocRecepExt' => $docRecepExt,
              'CodPaisRecep' => 'UY',
              'RznSocRecep' => $invoice->order->client->type === 'company' ? $invoice->order->client->company_name : $invoice->order->client->name . ' ' . $invoice->order->client->lastname,
              'DirRecep' => $invoice->order->client->address,
              'CiudadRecep' => $invoice->order->client->city,
              'DeptoRecep' => $invoice->order->client->state,
          ];
        }

        return $data;
    }

     /**
     * Actualiza la información de la empresa con la información de PyMo.
     *
     * @param Store $store
     * @param string $selectedBranchOfficeNumber
     * @param string|null $newCallbackUrl
     * @param string|null $pymoUser
     * @param string|null $newPymoPassword
    */
    public function updateStoreWithPymo(Store $store, ?string $selectedBranchOfficeNumber, ?string $newCallbackUrl, ?string $pymoUser, ?string $newPymoPassword): void
    {
        // Actualizar 'pymo_user' y 'pymo_password' antes de cualquier otra operación
        $this->updatePymoCredentials($store, $pymoUser, $newPymoPassword);

        // Reobtener el modelo de la empresa con los nuevos valores actualizados en la base de datos
        $store->refresh();

        // Obtener la información actual de la empresa desde PyMo
        $companyInfo = $this->getCompanyInfo($store);

        if (!$companyInfo) {
            Log::error('No se encontró la información de la empresa para la actualización de la empresa.');
            return;
        }

        // Buscar la sucursal seleccionada en la respuesta de la API de PyMo
        $branchOffices = $companyInfo['branchOffices'] ?? [];
        $selectedBranchOffice = collect($branchOffices)->firstWhere('number', $selectedBranchOfficeNumber);

        // Actualizamos la sucursal de la empresa
        $updateData = [
            'pymo_user' => $store->pymo_user,
            'pymo_branch_office' => $selectedBranchOfficeNumber,
        ];

        // Actualizar el store en la base de datos
        $store->update($updateData);

        // Verificar si hay cambios en el callbackNotificationUrl
        if ($selectedBranchOffice && $newCallbackUrl && $selectedBranchOffice['callbackNotificationUrl'] !== $newCallbackUrl) {
            // Actualizar el callbackNotificationUrl mediante la API de PyMo
            $this->updateBranchOfficeCallbackUrl($companyInfo, $store, $selectedBranchOfficeNumber, $newCallbackUrl);
        }
    }

    /**
     * Actualiza las credenciales de PyMo en la empresa.
     *
     * @param Store $store
     * @param string|null $pymoUser
     * @param string|null $newPymoPassword
     * @return void
    */
    private function updatePymoCredentials(Store $store, ?string $pymoUser, ?string $newPymoPassword): void
    {
        // Verificar si se ha proporcionado una nueva contraseña para PyMo
        if ($newPymoPassword && $newPymoPassword !== $store->pymo_password) {
            $encryptedPassword = Crypt::encryptString($newPymoPassword);

            // Actualizar pymo_user y pymo_password en la base de datos
            $store->update([
                'pymo_user' => $pymoUser, // Asumimos que este valor ya está definido en el modelo Store
                'pymo_password' => $encryptedPassword,
            ]);
        }
    }

    /**
     * Actualiza el callbackNotificationUrl de una sucursal en PyMo.
     *
     * @param array $companyInfo
     * @param Store $store
     * @param string $branchOfficeNumber
     * @param string $newCallbackUrl
     * @return bool
    */
    private function updateBranchOfficeCallbackUrl(array $companyInfo, string $store, string $branchOfficeNumber, string $newCallbackUrl): bool
    {
        if (!$companyInfo) {
            Log::error('No se encontró la información de la empresa para actualizar el callbackNotificationUrl.');
            return false;
        }

        Log::info('Información de la empresa:', $companyInfo);

        // Actualizar el callbackNotificationUrl de la sucursal correspondiente
        $branchOffices = $companyInfo['branchOffices'] ?? [];
        foreach ($branchOffices as &$branchOffice) {
            if ($branchOffice['number'] == $branchOfficeNumber) {
                $branchOffice['callbackNotificationUrl'] = $newCallbackUrl;
                break;
            }
        }

        Log::info('Sucursales actualizadas:', $branchOffices);

        // Actualizo de la variable companyInfo el campo branchOffices con la nueva información
        $companyInfo['branchOffices'] = $branchOffices;

        // Información de la empresa actualizada
        Log::info('Información de la empresa actualizada:', $companyInfo);

        // Preparar el payload para la solicitud de actualización
        $payload = [
            'payload' => [
                'company' => $companyInfo,
            ]
        ];

        // Enviar la solicitud de actualización a PyMo
        try {
            $cookies = $this->login($store);

            if (!$cookies) {
                Log::error('No se pudo iniciar sesión para obtener la información de la empresa.');
                return null;
            }

            $rut = $store->rut;

            $response = Http::withCookies($cookies, parse_url(env('PYMO_HOST'), PHP_URL_HOST))
                ->put(env('PYMO_HOST') . ':' . env('PYMO_PORT') . '/' . env('PYMO_VERSION') . '/companies/' . $rut, $payload);

            if ($response->successful()) {
                Log::info('El callbackNotificationUrl de la sucursal se actualizó correctamente.');
                return true;
            } else {
                Log::error('Error al actualizar el callbackNotificationUrl: ' . $response->body());
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Excepción al actualizar el callbackNotificationUrl: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Verifica el estado de un CFE en PyMo y lo actualiza en la base de datos.
     *
     * @param string $rut
     * @param string $branchOffice
     * @param string $urlToCheck
     * @return void
    */
    public function checkCfeStatus(string $rut, string $branchOffice, string $urlToCheck): void
    {
        // Busco la Store con el RUT y branch office
        Log::info('Rut de la empresa Webhook: ' . $rut);
        Log::info('Branch Office Webhook: ' . $branchOffice);

        $store = Store::where('rut', $rut)
            ->where('pymo_branch_office', $branchOffice)
            ->first();

        if (!$store) {
            Log::error('No se encontró la empresa con el RUT y sucursal especificados.');
            return;
        }

        // Iniciar sesión en PyMo
        $cookies = $this->login($store);

        if (!$cookies) {
            Log::error('No se pudo iniciar sesión para verificar el estado del CFE.');
            return;
        }

        // Construir la URL completa
        $url = env('PYMO_HOST') . ':' . env('PYMO_PORT') . $urlToCheck;

        // Realizar la solicitud para verificar el estado del CFE
        try {
            $response = Http::withCookies($cookies, parse_url(env('PYMO_HOST'), PHP_URL_HOST))
                ->get($url);

            if ($response->successful()) {
                $responseData = $response->json();

                Log::info('Respuesta Webhook Notification URL: ', $responseData);

                if (isset($responseData['payload']['branchOfficeSentCfes']) && is_array($responseData['payload']['branchOfficeSentCfes'])) {
                    foreach ($responseData['payload']['branchOfficeSentCfes'] as $cfeData) {
                        Log::info('CFE llegado de PYMO: ', $cfeData);
                        $this->updateCfeStatus($cfeData);
                    }
                }
            } else {
                Log::error('Error al verificar el estado del CFE: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('Excepción al verificar el estado del CFE: ' . $e->getMessage());
        }
    }

    /**
     * Actualiza el estado de un CFE en la base de datos basado en la respuesta de PyMo.
     *
     * @param array $cfeData
     * @return void
    */
    private function updateCfeStatus(array $cfeData): void
    {
        // Buscar el CFE en la base de datos usando el ID de emisión del cliente
        $cfe = CFE::where('cfeId', $cfeData['_id'])->first();

        if ($cfe) {
            // Actualizar el estado del CFE
            $cfe->status = $cfeData['actualCfeStatus'];
            $cfe->save();

            Log::info('Estado del CFE actualizado: ' . $cfe->cfeId . ' a ' . $cfeData['actualCfeStatus']);
        } else {
            Log::warning('No se encontró un CFE con ID: ' . $cfeData['clientEmissionId']);
        }
    }

    /**
     * Actualiza el estado de todos los CFEs para una empresa específica.
     *
     * @param Store $store
     * @return void
    */
    public function updateAllCfesForStore(Store $store): void
    {
        // Construir la URL para obtener todos los estados actualizados
        $url = env('PYMO_HOST') . ':' . env('PYMO_PORT') . '/v1/companies/' . $store->rut . '/sentCfes/' . $store->pymo_branch_office;

        // Iniciar sesión en PyMo
        $cookies = $this->login($store);

        if (!$cookies) {
            Log::error('No se pudo iniciar sesión en PyMo para la empresa con RUT: ' . $store->rut);
            return;
        }

        // Realizar la solicitud a PyMo para obtener los CFEs
        try {
            $response = Http::withCookies($cookies, parse_url(env('PYMO_HOST'), PHP_URL_HOST))->get($url);

            if ($response->successful()) {
                $cfesData = $response->json();

                Log::info('CFEs para la empresa con RUT: ' . $store->rut, $cfesData);

                // Actualizar el estado de cada CFE en la base de datos
                foreach ($cfesData['payload']['branchOfficeSentCfes'] as $cfeData) {
                    $this->updateCfeStatus($cfeData);
                }

                Log::info('Los estados de los CFEs para la empresa con RUT: ' . $store->rut . ' se han actualizado correctamente.');
            } else {
                Log::error('Error al obtener los CFEs para la empresa con RUT: ' . $store->rut . ' - ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('Excepción al obtener los CFEs para la empresa con RUT: ' . $store->rut . ' - ' . $e->getMessage());
        }
    }

    /**
     * Actualiza el estado de todos los CFEs para todas las empresas.
     *
     * @return void
    */
    public function updateAllCfesStatusForAllStores(): void
    {
        // Obtener todas las empresas que tengan invoices_enabled y datos en pymo_user y pymo_password, pymo_branch_office
        $stores = Store::where('invoices_enabled', true)
            ->whereNotNull('pymo_user')
            ->whereNotNull('pymo_password')
            ->whereNotNull('pymo_branch_office')
            ->get();

        // Actualizar el estado de los CFEs para cada empresa
        foreach ($stores as $store) {
            $this->updateAllCfesForStore($store);
        }
    }

    /**
     * Obtiene y almacena los CFEs recibidos para una empresa específica.
     *
     * @param Store $store
     * @return array|null
    */
    public function processReceivedCfes(Store $store): ?array
    {
        $rut = $store->rut;

        if (!$rut) {
            Log::error('No se pudo obtener el RUT de la empresa.');
            return null;
        }

        try {
            // Obtener las cookies para la autenticación
            $cookies = $this->login($store);

            if (!$cookies) {
                Log::error('Error al iniciar sesión en el servicio PyMo.');
                return null;
            }

            // Obtener los recibos desde el endpoint
            $receivedCfes = $this->fetchReceivedCfes($rut, $cookies);

            if (!$receivedCfes) {
                Log::info('No se encontraron CFEs recibidos.');
                return [];
            }

            foreach ($receivedCfes as $receivedCfe) {
                // Verificar si existe la clave 'CFE' en el recibo
                if (!isset($receivedCfe['CFE'])) {
                    Log::error('El recibo no tiene la estructura esperada: ' . json_encode($receivedCfe));
                    continue; // Omitir este recibo y pasar al siguiente
                }

                // Obtener dinámicamente la primera clave dentro de 'CFE'
                $cfe = $receivedCfe['CFE'];
                $firstKey = array_key_first($cfe); // Obtener la primera clave dentro de 'CFE'

                if (!isset($cfe[$firstKey])) {
                    Log::error('No se pudo obtener la estructura interna del CFE: ' . json_encode($receivedCfe));
                    continue; // Omitir este recibo si no se encuentra la estructura esperada
                }

                $cfeData = $cfe[$firstKey];

                // Extraer los datos del recibo desde la estructura seleccionada
                $idDoc = $cfeData['Encabezado']['IdDoc'] ?? [];
                $totales = $cfeData['Encabezado']['Totales'] ?? [];
                $caeData = $cfeData['CAEData'] ?? [];
                $adenda = $receivedCfe['Adenda'] ?? null;

                Log::info('Datos de total: ' . $totales['TpoMoneda']);

                $cfeEntry = [
                    'store_id' => $store->id,
                    'type' => $idDoc['TipoCFE'] ?? null,
                    'serie' => $idDoc['Serie'] ?? null,
                    'nro' => $idDoc['Nro'] ?? null,
                    'caeNumber' => $caeData['CAE_ID'] ?? null,
                    'caeRange' => json_encode([
                        'first' => $caeData['DNro'] ?? null,
                        'last' => $caeData['HNro'] ?? null,
                    ]),
                    'caeExpirationDate' => $caeData['FecVenc'] ?? null,
                    'total' => $totales['MntTotal'] ?? 0,
                    'currency' => $totales['TpoMoneda'] ?? 'UYU',
                    'status' => $receivedCfe['cfeStatus'] ?? 'PENDING_REVISION',
                    'balance' => $totales['MntTotal'] ?? 0,
                    'received' => true,
                    'emitionDate' => $idDoc['FchEmis'] ?? null,
                    'cfeId' => $receivedCfe['_id'] ?? null,
                    // La adenda puede ser un objeto, la convierto siempre a string
                    'reason' => $adenda ? json_encode($adenda) : null,
                    'issuer_name' => $cfeData['Encabezado']['Emisor']['NomComercial'] ?? null,
                    'is_receipt' => ($idDoc['TipoCFE'] ?? null) == '111',
                ];

                Log::info('CFE a procesar: ', $cfeEntry);

                // Validar si los campos requeridos existen antes de crear o actualizar el CFE
                if (is_null($cfeEntry['type']) || is_null($cfeEntry['serie']) || is_null($cfeEntry['nro'])) {
                    Log::error('El recibo no tiene los campos obligatorios: ' . json_encode($receivedCfe));
                    continue; // Omitir este recibo y pasar al siguiente
                }

                // Actualizar o crear el CFE en la base de datos
                CFE::updateOrCreate(
                    [
                        'type' => $cfeEntry['type'],
                        'serie' => $cfeEntry['serie'],
                        'nro' => $cfeEntry['nro'],
                    ],
                    $cfeEntry
                );
            }

            // Retornar los CFEs actualizados de la base de datos
            return CFE::where('received', true)->get()->toArray();
        } catch (\Exception $e) {
            Log::error('Error al procesar los recibos recibidos: ' . $e->getMessage());
            return null;
        }
    }


    /**
     * Prepara los datos de los CFEs recibidos para ser usados en DataTables.
     *
     * @param Store|null $store
     * @return Collection
    */
    public function getReceivedCfesDataForDatatables(?Store $store = null): Collection
    {
        $validTypes = [101, 102, 103, 111, 112, 113]; // Tipos válidos de CFE

        // Si se proporciona una empresa específica, filtrar por esta empresa
        if ($store) {
            $cfes = CFE::with('order.client', 'order.store')
                ->where('store_id', $store->id)
                ->whereIn('type', $validTypes)
                ->where('received', true)
                ->orderBy('emitionDate', 'desc')
                ->get();
        } else {
            // Si no se proporciona una empresa específica, obtener todos los CFEs recibidos
            $cfes = CFE::with('order.client', 'order.store')
                ->whereIn('type', $validTypes)
                ->where('received', true)
                ->orderBy('emitionDate', 'desc')
                ->get();
        }

        $totalItems = $cfes->count(); // Obtener la cantidad total de elementos

        // Formatear la colección de datos para el DataTable
        return $cfes->map(function ($cfe, $index) use ($totalItems) {
          $typeCFEs = [
            101 => 'eTicket',
            102 => 'eTicket - Nota de Crédito',
            103 => 'eTicket - Nota de Débito',
            111 => 'eFactura',
            112 => 'eFactura - Nota de Crédito',
            113 => 'eFactura - Nota de Débito',
          ];

          if ($cfe->is_receipt) {
              $typeCFEs[101] = 'eTicket - Recibo';
              $typeCFEs[111] = 'eFactura - Recibo';
          }

          if (
              !$cfe->is_receipt &&
              in_array($cfe->type, [101, 111]) &&
              $cfe->relatedCfes->count() > 0 &&
              $cfe->relatedCfes->contains(function ($relatedCfe) use ($cfe) {
                  return $relatedCfe->type == $cfe->type;
              })
          ) {
              $cfe->hide_emit = true;
          }

          return [
              'id' => $totalItems - $index,
              'date' => $cfe->emitionDate,
              'issuer_name' => $cfe->issuer_name ?? 'N/A',
              'type' => $typeCFEs[$cfe->type] ?? 'N/A',
              'currency' => $cfe->currency,
              'total' => $cfe->total,
              'qrUrl' => $cfe->qrUrl,
              'serie' => $cfe->serie,
              'cfeId' => $cfe->cfeId,
              'nro' => $cfe->nro,
              'balance' => $cfe->balance,
              'caeNumber' => $cfe->caeNumber,
              'caeRange' => $cfe->caeRange,
              'caeExpirationDate' => $cfe->caeExpirationDate,
              'sentXmlHash' => $cfe->sentXmlHash,
              'securityCode' => $cfe->securityCode,
              'reason' => $cfe->reason,
              'associated_id' => $cfe->main_cfe_id,
              'is_receipt' => $cfe->is_receipt,
              'hide_emit' => $cfe->hide_emit,
              'status' => $cfe->status ?? 'N/A'
          ];
      });
    }

    public function getActiveCaes(Store $store): ?array
    {
        $caeTypes = [
            '101' => 'eTicket',
            '102' => 'eTicket - Nota de Crédito',
            '103' => 'eTicket - Nota de Débito',
            '111' => 'eFactura',
            '112' => 'eFactura - Nota de Crédito',
            '113' => 'eFactura - Nota de Débito',
        ];

        $cookies = $this->login($store);

        if (!$cookies) {
            Log::error('No se pudo iniciar sesión para obtener los CAEs activos.');
            return null;
        }

        $rut = $store->rut;

        if (!$rut) {
            Log::error('No se encontró el RUT de la empresa.');
            return null;
        }

        $results = [];

        foreach ($caeTypes as $type => $typeName) {
            $url = env('PYMO_HOST') . ':' . env('PYMO_PORT') . '/' . env('PYMO_VERSION') . '/companies/' . $rut . '/cfesActiveNumbers/' . $type;

            Log::info("Consultando URL: {$url}"); // Log para depuración

            try {
                $response = Http::withCookies($cookies, parse_url(env('PYMO_HOST'), PHP_URL_HOST))->get($url);

                if ($response->successful()) {
                    $jsonResponse = $response->json();
                    Log::info("Respuesta exitosa para {$type}: ", $jsonResponse);

                    $companyCfeActiveNumbers = $jsonResponse['payload']['companyCfeActiveNumbers'] ?? [];

                    if (!is_array($companyCfeActiveNumbers)) {
                        $companyCfeActiveNumbers = [$companyCfeActiveNumbers];
                    }

                    foreach ($companyCfeActiveNumbers as $range) {
                        $results[] = [
                            'type' => $typeName,
                            'nextNum' => $range['nextNum'] ?? 'N/A',
                            'range' => [
                                'first' => $range['range']['first'] ?? 'N/A',
                                'last' => $range['range']['last'] ?? 'N/A',
                            ],
                        ];
                    }
                } else {
                    Log::error("Error en la API para {$type}: " . $response->status() . ' - ' . $response->body());
                }
            } catch (\Exception $e) {
                Log::error("Excepción al consultar {$type}: " . $e->getMessage());
            }
        }

        return $results;
    }


    public function uploadCaeToPymo(Store $store, string $type, $file): array
    {
        $cookies = $this->login($store);

        if (!$cookies) {
            Log::error('No se pudo iniciar sesión para cargar el CAE.');
            return [
                'success' => false,
                'message' => 'No se pudo iniciar sesión en PyMo.',
                'statusCode' => 401,
            ];
        }

        $rut = $store->rut;

        if (!$rut) {
            Log::error('No se encontró el RUT de la empresa.');
            return [
                'success' => false,
                'message' => 'El RUT de la tienda no está configurado.',
                'statusCode' => 400,
            ];
        }

        $url = env('PYMO_HOST') . ':' . env('PYMO_PORT') . '/' . env('PYMO_VERSION') . "/companies/{$rut}/cfesActiveNumbers/{$type}/upload-xml";
        Log::info("Subiendo archivo a URL: {$url}");

        try {
            $response = Http::withCookies($cookies, parse_url(env('PYMO_HOST'), PHP_URL_HOST))
                ->attach(
                    'CfesNewNumbers',
                    file_get_contents($file->getRealPath()),
                    $file->getClientOriginalName()
                )
                ->post($url);

            if ($response->successful()) {
                $responseData = $response->json();
                Log::info('Archivo subido exitosamente:', $responseData);
                return [
                    'success' => true,
                    'message' => $responseData['message']['value'] ?? 'Archivo subido correctamente.',
                ];
            }

            Log::error('Error en la API de PyMo:', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'success' => false,
                'message' => 'Error en la API de PyMo: ' . ($response->json()['message']['value'] ?? 'Error desconocido.'),
                'statusCode' => $response->status(),
            ];
        } catch (\Exception $e) {
            Log::error("Excepción al cargar CAE: {$e->getMessage()}");
            return [
                'success' => false,
                'message' => 'Ocurrió un error inesperado.',
                'statusCode' => 500,
            ];
        }
    }


    /**
     * Emite un CFE (eFactura o eTicket) para una entidad facturable (Income, Order, etc.).
     *
     * @param Model $entity
     * @param float|null $amountToBill
     * @param int|null $payType
     * @return void
     */
    public function emitCFEFree($entity, ?float $amountToBill = null, ?int $payType = 1): void
    {
        $store = $entity->store;

        // Iniciar sesión para obtener las cookies necesarias
        $cookies = $this->login($store);

        if (!$cookies) {
            Log::error('No se pudo iniciar sesión para emitir el CFE.');
            throw new CFEException('No se pudo iniciar sesión para emitir el CFE.');
        }

        $rut = $store->rut;
        $branchOffice = $store->pymo_branch_office;

        if (!$rut) {
            Log::error('No se encontró el RUT de la empresa para emitir el CFE.');
            throw new CFEException('No se encontró el RUT de la empresa para emitir el CFE.');
        }

        if (!$branchOffice) {
            Log::error('No se encontró la sucursal de la empresa para emitir el CFE.');
            throw new CFEException('No se encontró la sucursal de la empresa para emitir el CFE.');
        }

        // Determinar el cliente o entidad asociada a la operación
        $client = $entity->client ?? null;

        // Determinar el tipo de CFE
        $cfeType = '101'; // Por defecto, eTicket
        if ($client) {
            $cfeType = $client->type === 'company' ? '111' : '101'; // '111' para empresas, '101' para individuos
        }

        // Construir URL para emitir CFE
        $url = env('PYMO_HOST') . ':' . env('PYMO_PORT') . '/' . env('PYMO_VERSION') . '/companies/' . $rut . '/sendCfes/' . $branchOffice;

        // Calcular el monto a facturar
        $amountToBill = $amountToBill ?? $entity->total ?? $entity->income_amount;

        // Preparar datos del CFE
        $cfeData = $this->prepareCFEDataFree($entity, $cfeType, $amountToBill, $payType);

        Log::info('Datos del CFE:', $cfeData);

        try {
            $payloadArray = [
                'emailsToNotify' => [],
                $cfeType => [$cfeData],
            ];

            $payload = (object) $payloadArray;

            // Hacer la solicitud para emitir el CFE
            $response = Http::withCookies($cookies, parse_url(env('PYMO_HOST'), PHP_URL_HOST))
                ->asJson()
                ->post($url, $payload);

            if ($response->successful()) {
                Log::info('CFE emitido correctamente: ' . $response->body());

                $responseData = $response->json();

                foreach ($responseData['payload']['cfesIds'] as $cfe) {
                    try {
                        // Crear el recibo asociado a la entidad
                        $invoice = $entity->cfes()->create([
                            'store_id' => $store->id,
                            'type' => $cfeType,
                            'serie' => $cfe['serie'],
                            'nro' => $cfe['nro'],
                            'caeNumber' => $cfe['caeNumber'],
                            'caeRange' => json_encode($cfe['caeRange']),
                            'caeExpirationDate' => $cfe['caeExpirationDate'],
                            'total' => $amountToBill,
                            'balance' => $amountToBill,
                            'emitionDate' => $cfe['emitionDate'],
                            'sentXmlHash' => $cfe['sentXmlHash'],
                            'securityCode' => $cfe['securityCode'],
                            'qrUrl' => $cfe['qrUrl'],
                            'cfeId' => $cfe['id'],
                            'status' => $cfeType === '101' ? 'SCHEDULED_WITHOUT_CAE_NRO' : 'CREATED_WITHOUT_CAE_NRO',
                        ]);

                        Log::info('Receipt creado correctamente:', $invoice->toArray());
                    } catch (\Exception $e) {
                        Log::error('Error al crear el recibo asociado al CFE: ' . $e->getMessage());
                        throw new CFEException('Error al crear el recibo asociado al CFE: ' . $e->getMessage());
                    }
                }
            } else {
                Log::error('Error al emitir CFE: ' . $response->body());
                throw new CFEException('Error al emitir CFE: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('Excepción al emitir CFE: ' . $e->getMessage());
            throw new CFEException('Excepción al emitir CFE: ' . $e->getMessage());
        }
    }

    /**
     * Prepara los datos necesarios para emitir el CFE.
     *
     * @param Model $entity Entidad facturable (Income, Order, etc.)
     * @param string $cfeType Tipo de CFE (eTicket, eFactura, etc.)
     * @param float $amountToBill Monto a facturar
     * @param int $payType Forma de pago (1 = Contado, 2 = Crédito)
     * @return array Datos preparados para emitir el CFE
     */
    private function prepareCFEDataFree($entity, string $cfeType, float $amountToBill, int $payType): array
    {
        // Determinar cliente (opcional) según la entidad
        $client = $entity->client ?? null;

        // Obtener los items (products para Order, items para Income)
        $itemsData = $entity->items ?? $entity->products;
        $items = is_string($itemsData) ? json_decode($itemsData, true) : $itemsData;

        if (!is_array($items)) {
            throw new \Exception('No se encontraron items válidos en la entidad.');
        }

        // Calcular proporción para el monto a facturar
        $totalEntity = $entity->total ?? $entity->income_amount;
        $proportion = ($amountToBill < $totalEntity) ? $amountToBill / $totalEntity : 1;

        $ivaTasaBasica = 22; // Tasa de IVA básica
        $subtotalConIVA = 0;
        $totalDescuento = 0;

        $items = array_map(function ($item, $index) use ($proportion, &$subtotalConIVA, &$totalDescuento, $ivaTasaBasica) {
            $adjustedAmount = round($item['quantity'] * $proportion, 2);
            $itemPriceConIVA = round($item['price'], 2);

            // Calcular descuento
            $discountPercentage = $item['discount'] ?? 0; // Si no hay descuento, usar 0
            $discountAmount = round($itemPriceConIVA * ($discountPercentage / 100), 2);

            $totalDescuento += $discountAmount * $adjustedAmount;
            $subtotalConIVA += ($itemPriceConIVA - $discountAmount) * $adjustedAmount;

            return [
                'NroLinDet' => $index + 1,
                'IndFact' => 3, // Índice de facturación (definir según necesidades)
                'NomItem' => $this->cleanProductName($item['name']),
                'Cantidad' => $adjustedAmount,
                'UniMed' => 'N/A',
                'DescuentoPct' => round($discountPercentage, 2),
                'DescuentoMonto' => $discountAmount,
                'MontoItem' => round(($itemPriceConIVA - $discountAmount) * $adjustedAmount, 2),
                'PrecioUnitario' => $itemPriceConIVA,
            ];
        }, $items, array_keys($items));

        $subtotalConIVA = round($subtotalConIVA, 2);

        // Preparar datos finales para el CFE
        $cfeData = [
            'clientEmissionId' => $entity->uuid ?? $entity->id . now()->timestamp,
            'adenda' => 'Entidad ' . $entity->id . ' - Sumeria.',
            'IdDoc' => [
                'MntBruto' => 1,
                'FmaPago' => $payType,
            ],
            'Receptor' => (object) [],
            'Totales' => [
                'TpoMoneda' => 'UYU',
                // 'TpoCambio' => $exchangeRate, // Activar si manejas tipo de cambio
            ],
            'Items' => $items,
        ];

        // Agregar datos del cliente, si existe
        if ($client) {
            $cfeData['Receptor'] = [
                'TipoDocRecep' => $client->type === 'company' ? 2 : 3,
                'CodPaisRecep' => 'UY',
                'RznSocRecep' => $client->type === 'company' ? $client->company_name : $client->name . ' ' . $client->lastname,
                'DirRecep' => $client->address,
                'CiudadRecep' => $client->city,
                'DeptoRecep' => $client->state,
            ];

            if ($client->type === 'company' && $client->rut) {
                $cfeData['Receptor']['DocRecep'] = $client->rut;
            } elseif ($client->type === 'individual' && $client->ci) {
                $cfeData['Receptor']['DocRecep'] = $client->ci;
            } else {
                Log::error('Cliente sin documento adecuado para DocRecep en la entidad ' . $entity->id);
            }
        }

        // Agregar fecha de emisión
        if ($cfeType === '101') {
            $cfeData['IdDoc']['FchEmis'] = now()->toIso8601String();
        }

        return $cfeData;
    }

    /**
     * Obtiene el PDF de un CFE asociado a una entidad relacionada.
     *
     * @param Model $relatedEntity Entidad relacionada que tiene la relación polimórfica con CFEs (e.g., Income, Order, etc.).
     * @return Response Respuesta con el contenido del PDF del CFE, o una excepción en caso de error.
     *
     * @throws \Exception Si la entidad no tiene una relación válida con CFEs o si ocurre un error al obtener el PDF.
     *
     */

    public function getCfePdfFree($relatedEntity): Response
    {
        // Asegúrate de que el modelo tenga la relación polimórfica `cfes`
        if (!method_exists($relatedEntity, 'cfes')) {
            throw new \Exception('El modelo proporcionado no tiene la relación requerida con CFEs.');
        }

        // Obtener el CFE relacionado
        $cfe = $relatedEntity->cfes()->first();
        if (!$cfe) {
            throw new \Exception('No se encontró un CFE asociado a esta entidad.');
        }

        $store = $cfe->store;

        if (!$store) {
            throw new \Exception('No se encontró la tienda asociada al CFE.');
        }

        $rut = $store->rut;
        $branchOffice = $store->pymo_branch_office;

        if (!$rut) {
            throw new \Exception('No se encontró el RUT de la empresa para obtener el PDF del CFE.');
        }

        if (!$branchOffice) {
            throw new \Exception('No se encontró la sucursal de la empresa para obtener el PDF del CFE.');
        }

        // Realizar el login para obtener cookies de autenticación
        $cookies = $this->login($store);

        if (!$cookies) {
            throw new \Exception('No se pudo iniciar sesión para obtener el PDF del CFE.');
        }

        // Construir la URL para obtener el PDF
        $cfeId = $cfe->cfeId;
        $url = env('PYMO_HOST') . ':' . env('PYMO_PORT') . '/' . env('PYMO_VERSION') . '/companies/' . $rut . '/invoices/?id=' . $cfeId;

        try {
            // Hacer la solicitud para obtener el PDF
            $response = Http::withCookies($cookies, parse_url(env('PYMO_HOST'), PHP_URL_HOST))
                ->asJson()
                ->get($url);

            if ($response->successful()) {
                $pdfContent = $response->body();

                // Enviar el PDF al navegador
                return response($pdfContent)
                    ->header('Content-Type', 'application/pdf')
                    ->header('Content-Disposition', 'attachment; filename="CFE_' . $cfeId . '.pdf"');
            } else {
                Log::error('Error al obtener el PDF del CFE: ' . $response->body());
                throw new \Exception('Error al obtener el PDF del CFE.');
            }
        } catch (\Exception $e) {
            Log::error('Excepción al obtener el PDF del CFE: ' . $e->getMessage());
            throw new \Exception('Error al obtener el PDF del CFE: ' . $e->getMessage());
        }
    }

}
