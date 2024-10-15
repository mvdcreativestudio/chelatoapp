<?php

namespace App\Repositories;

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
              'client_name' => $invoice->order->client->name ?? 'N/A',
              'client_email' => $invoice->order->client->email ?? 'N/A',
              'client_lastname' => $invoice->order->client->lastname ?? 'N/A',
              'date' => $invoice->emitionDate,
              'order_id' => $invoice->order->id,
              'type' => $typeCFEs[$invoice->type] ?? 'N/A',
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
              'status' => $invoice->status ?? 'N/A',
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
              'client_name' => $invoice->order->client->name ?? 'N/A',
              'client_email' => $invoice->order->client->email ?? 'N/A',
              'client_lastname' => $invoice->order->client->lastname ?? 'N/A',
              'date' => $invoice->emitionDate,
              'order_id' => $invoice->order->id,
              'type' => $typeCFEs[$invoice->type] ?? 'N/A',
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
              'status' => $invoice->status ?? 'N/A'
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

      $logoResponse = Http::withCookies($cookies, parse_url(env('PYMO_HOST'), PHP_URL_HOST))
          ->attach('logo', $logo->get(), 'logo.jpg')
          ->post(env('PYMO_HOST') . ':' . env('PYMO_PORT') . '/' . env('PYMO_VERSION') . '/companies/' . $rut . '/logo');

      return $logoResponse->successful();
    }

    /**
     * Obtiene el logo de la empresa y lo guarda localmente.
     *
     * @param Store $store
     * @return string|null
    */
    public function getCompanyLogo(Store $store): ?string
    {
        $cookies = $this->login($store);

        if (!$cookies) {
            Log::error('No se pudo iniciar sesión para obtener el logo de la empresa.');
            return null;
        }

        if (!$store->rut) {
            Log::error('No se encontró el RUT de la empresa para obtener el logo de la empresa.');
            return null;
        }

        // Construir la URL para obtener el logo de la empresa
        $url = env('PYMO_HOST') . ':' . env('PYMO_PORT') . '/' . env('PYMO_VERSION') . '/companies/' . $store->rut . '/logo';

        try {
            $logoResponse = Http::withCookies($cookies, parse_url(env('PYMO_HOST'), PHP_URL_HOST))
                ->get($url);

            if ($logoResponse->failed()) {
                Log::error('Error al obtener el logo de la empresa: ' . $logoResponse->body());
                return null;
            }

            return $this->saveLogoLocally($logoResponse->body());
        } catch (\Exception $e) {
            Log::error('Excepción al obtener el logo de la empresa: ' . $e->getMessage());
            return null;
        }
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
     * @return void
    */
    public function emitCFE(Order $order, ?float $amountToBill = null, ?int $payType = 1): void
    {
        $store = $order->store;

        $cookies = $this->login($store);

        if (!$cookies) {
            Log::error('No se pudo iniciar sesión para emitir el CFE.');
            return;
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

            $url = env('PYMO_HOST') . ':' . env('PYMO_PORT') . '/' . env('PYMO_VERSION') . '/companies/' . $rut . '/sendCfes/' . $branchOffice;

            $amountToBill = $amountToBill ?? $order->total;

            $cfeData = $this->prepareCFEData($order, $cfeType, $amountToBill, $payType);

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
                Log::error('Excepción al emitir CFE: ' . $e->getMessage());
            }
        } else {
            Log::error('No se encontró el RUT de la empresa para emitir el CFE.');
        }
    }

    /**
     * Prepara los datos necesarios para emitir el CFE.
     *
     * @param Order $order
     * @param string $cfeType
     * @param float $amountToBill
     * @param int $payType
     * @return array
    */
    private function prepareCFEData(Order $order, string $cfeType, float $amountToBill, int $payType): array
    {
        $client = $order->client;
        $products = is_string($order->products) ? json_decode($order->products, true) : $order->products;

        // // Activar si se necesita obtener la tasa de cambio más cercana a la fecha de la orden (se vende en USD)
        // $usdRate = CurrencyRate::where('name', 'Dólar')
        //     ->first()
        //     ->histories()
        //     ->orderByRaw('ABS(TIMESTAMPDIFF(SECOND, date, ?))', [$order->created_at])
        //     ->first(); // Obtener la tasa de cambio más cercana a la fecha de la orden

        // Log::info('Tasa de cambio: ' . $usdRate);

        // if ($usdRate) {
        //     $exchangeRate = (float) $usdRate->sell;
        // } else {
        //     throw new \Exception('No se encontró el tipo de cambio para el dólar.');
        // }

        $proportion = ($amountToBill < $order->total) ? $amountToBill / $order->total : 1;

        $ivaTasaBasica = 22;
        $subtotalConIVA = 0;
        $totalDescuento = 0; // Inicializar el total de descuento

        // Generar los ítems basados en el precio original de la orden
        $items = array_map(function ($product, $index) use ($proportion, $order, &$subtotalConIVA, &$totalDescuento, $ivaTasaBasica) {
            $adjustedAmount = round($product['quantity'] * $proportion, 0);

            $discountPercentage = round((($order->subtotal - $order->total) / $order->subtotal) * 100, 0);

            // Precio unitario del producto de la orden (con IVA incluido)
            $productPriceConIVA = round($product['price'], 2);

            // Descuento aplicado al precio con IVA
            $discountAmount = round($productPriceConIVA * ($discountPercentage / 100), 2);

            // Acumular el total de descuento
            $totalDescuento += $discountAmount * $adjustedAmount;

            // Acumular el subtotal con IVA incluido
            $subtotalConIVA += ($productPriceConIVA - $discountAmount) * $adjustedAmount;

            // Limpiar y limitar el nombre del producto a 50 caracteres
            $cleanedProductName = $this->cleanProductName($product['name']);

            return [
                'NroLinDet' => $index + 1, // Número de línea de detalle
                'IndFact' => 3, // Gravado a Tasa Básica
                'NomItem' => $cleanedProductName, // Nombre del producto limpio
                'Cantidad' => $adjustedAmount, // Cantidad del producto
                'UniMed' => 'N/A', // Unidad de medida, si no tiene usar N/A
                "DescuentoPct" => $discountPercentage, // % de descuento aplicado
                "DescuentoMonto" => $discountAmount, // Monto de descuento por unidad
                "MontoItem" => round(($productPriceConIVA - $discountAmount) * $adjustedAmount, 2), // Monto del ítem con IVA
                'PrecioUnitario' => $productPriceConIVA, // Precio unitario del producto con IVA
            ];
        }, $products, array_keys($products));

        // Redondear los totales a dos decimales
        $subtotalConIVA = round($subtotalConIVA, 2);

        // Preparar los datos del CFE
        $cfeData = [
          'clientEmissionId' => $order->uuid,
          'adenda' => 'Orden ' . $order->id . ' - Sumeria.',
          'IdDoc' => [
              'MntBruto' => 1, // Indica que los montos enviados incluyen IVA
              'FmaPago' => $payType, // Al facturar manualmente se puede elegir si fue crédito o contado, si no asume que es contado.
          ],
          'Receptor' => (object) [], // Inicializar como objeto vacío
          'Totales' => [
              'TpoMoneda' => 'UYU', // Moneda de la factura
              'TpoCambio' => $exchangeRate, // Tipo de cambio
          ],
          'Items' => $items,
        ];

        // Comprobar si existe un cliente y no es de tipo 'no-client'
        if ($client && $client->type !== 'no-client') {
          $cfeData['Receptor'] = [
              'TipoDocRecep' => $client->type === 'company' ? 2 : 3, // 2 para RUC, 3 para CI
              'CodPaisRecep' => 'UY',
              'RznSocRecep' => $client->type === 'company' ? $client->company_name : $client->name . ' ' . $client->lastname,
              'DirRecep' => $client->address, // Dirección del cliente
              'CiudadRecep' => $client->city, // Ciudad del cliente
              'DeptoRecep' => $client->state, // Departamento del cliente
          ];

          // Añadir 'DocRecep' según el tipo de cliente
          if ($client->type === 'company') {
              $cfeData['Receptor']['DocRecep'] = $client->rut;
          } elseif ($client->type === 'individual') {
              $cfeData['Receptor']['DocRecep'] = $client->ci;
          }
        }


        if ($cfeType === '101') {
            $cfeData['IdDoc']['FchEmis'] = now()->toIso8601String();
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
        $notaData = $this->prepareNoteData($invoice, $noteAmount, $reason, $noteType);

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
     * @return array
    */
    private function prepareNoteData(CFE $invoice, float $noteAmount, string $reason, string $noteType): array
    {
        $order = $invoice->order;
        $usdRate = CurrencyRate::where('name', 'Dólar')->orderBy('date', 'desc')->first();

        if ($usdRate) {
            $exchangeRate = (float) str_replace(',', '.', $usdRate->sell);
        } else {
            throw new \Exception('No se encontró el tipo de cambio para el dólar.');
        }

      // Utilizar los datos del receptor del CFE existente
      $tipoDocRecep = $invoice->type == 111 ? 2 : 3; // 2 para RUC si es una eFactura, 3 para CI si es un eTicket
      $docRecep = $invoice->order->document ?? '12345678'; // Tomar el documento del receptor o '12345678' como predeterminado

      $notaData = [
          'clientEmissionId' => $order->uuid . '-' . $noteType . '-' . now()->timestamp,
        'adenda' => $reason,
        'IdDoc' => [
            'FchEmis' => now()->toIso8601String(),
            'FmaPago' => '1',
        ],
        'Receptor' => (object) [], // Inicializar como objeto vacío
        'Totales' => [
            'TpoMoneda' => 'UYU',
            // 'TpoCambio' => $exchangeRate,
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
                'NomItem' => 'Nota de ' . (ucfirst($noteType) == 'credit' ? 'Crédito' : 'Débito') . ' - Ajuste',
                'Cantidad' => '1',
                'UniMed' => 'N/A',
                'PrecioUnitario' => $noteAmount,
                'MontoItem' => $noteAmount,
            ]
        ],
        'Emisor' => [
            'GiroEmis' => 'base'
        ]
      ];

      // Comprobar si existe un cliente y no es de tipo 'no-client'
      if ($order->client && $order->client->type !== 'no-client') {
        $notaData['Receptor'] = [
            'TipoDocRecep' => $invoice->type == 111 ? 2 : 3, // 2 para RUC si es una eFactura, 3 para CI si es un eTicket
            'CodPaisRecep' => 'UY',
            'PaisRecep' => 'Uruguay',
            'DocRecep' => $order->client->type === 'company' ? $order->client->rut : $order->client->ci,
            'RznSocRecep' => $order->client->type === 'company' ? $order->client->company_name : $order->client->name . ' ' . $order->client->lastname,
            'DirRecep' => $order->client->address,
            'CiudadRecep' => $order->client->city,
            'DeptoRecep' => $order->client->state,
            'CompraID' => $order->id,
        ];

        if ($invoice->type == 111) {
            $notaData['IdDoc'] = array_merge($notaData['IdDoc'], [
                'ViaTransp' => '8',
                'ClauVenta' => 'N/A',
                'ModVenta' => '90'
            ]);
        }
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
     * @return void
     * @throws \Exception
    */
    public function emitReceipt(int $invoiceId): void
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
        $receiptData = $this->prepareReceiptData($invoice);

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
     * @return array
    */
    private function prepareReceiptData(CFE $invoice): array
    {
        $order = $invoice->order;

        // // Modificar si se vende en USD
        // // Obtener la tasa de cambio del historial de CurrencyRate
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

        $data = [
            'clientEmissionId' => $invoice->order->uuid . '-R',
            'adenda' => 'Recibo de Cobranza sobre ' . ($invoice->type == 111 ? 'eFactura' : 'eTicket'),
            'IdDoc' => [
                'IndCobPropia' => '1',
                'FmaPago' => '1',
            ],
            'Receptor' => (object) [], // Inicializar como objeto vacío
            'Totales' => [
                'TpoMoneda' => 'UYU',
                // Activar si se vende en USD
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
            $data['Receptor'] = [
                'TipoDocRecep' => $invoice->type == 111 ? 2 : 3, // 2 para RUC si es una eFactura, 3 para CI si es un eTicket
                'CodPaisRecep' => 'UY',
                'RznSocRecep' => $invoice->order->client->type === 'company' ? $invoice->order->client->company_name : $invoice->order->client->name . ' ' . $invoice->order->client->lastname,
                'DirRecep' => $invoice->order->client->address,
                'CiudadRecep' => $invoice->order->client->city,
                'DeptoRecep' => $invoice->order->client->state,
            ];

            // Agregar documento receptor si es una empresa o individuo
            if ($invoice->order->client->type === 'company') {
                $data['Receptor']['DocRecep'] = $invoice->order->client->rut;
            } elseif ($invoice->order->client->type === 'individual') {
                $data['Receptor']['DocRecep'] = $invoice->order->client->ci;
            }
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
                    'currency' => $totales['TpoMoneda'] ?? 'USD',
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
}

