<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Repositories\StoreRepository;
use App\Models\PosDevice;
use App\Http\Requests\StoreEmailConfigRequest;
use App\Enums\MercadoPago\MercadoPagoApplicationTypeEnum;
use App\Exceptions\MercadoPagoException;
use App\Http\Controllers\StoresEmailConfigController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use App\Repositories\AccountingRepository;
use App\Repositories\MercadoPagoAccountStoreRepository;
use App\Services\MercadoPagoService;

class IntegrationController extends Controller
{
    protected $storeRepository;
    protected $accountingRepository;
    protected $mpService;
    protected $mercadoPagoAccountStoreRepository;

    public function __construct(StoreRepository $storeRepository, AccountingRepository $accountingRepository, MercadoPagoService $mpService, MercadoPagoAccountStoreRepository $mercadoPagoAccountStoreRepository)
    {
        $this->storeRepository = $storeRepository;
        $this->accountingRepository = $accountingRepository;
        $this->mpService = $mpService;
        $this->mercadoPagoAccountStoreRepository = $mercadoPagoAccountStoreRepository;
    }

    /**
     * Muestra la vista principal de integraciones
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Cargar las tiendas con todas las relaciones necesarias
        $stores = $this->storeRepository->getAll()->load([
            'mercadoPagoAccount',
            'mercadoPagoAccountStore',
            'posDevices', // Relación de dispositivos POS
        ]);

        // Transformar las tiendas para incluir atributos adicionales
        $stores = $stores->map(function ($store) {
            if ($store->invoices_enabled && $store->pymo_user && $store->pymo_password) {
                $companyInfo = $this->accountingRepository->getCompanyInfo($store);
                $store->pymoInfo = $companyInfo;
                $store->branchOffices = $companyInfo['branchOffices'] ?? [];
            }

            // Filtrar los dispositivos para cada integración
            $store->ocaDevices = $store->posDevices->where('pos_provider_id', 4);
            $store->handyDevices = $store->posDevices->where('pos_provider_id', 3);
            $store->fiservDevices = $store->posDevices->where('pos_provider_id', 2);
            $store->scanntechDevices = $store->posDevices->where('pos_provider_id', 1);
            $store->otherDevices = $store->posDevices->whereNotIn('pos_provider_id', [1, 2, 3, 4]);

            // Añadir el atributo MercadoPagoOnline
            return $store->setAttribute(
                'mercadoPagoOnline',
                $store->mercadoPagoAccount->firstWhere('type', MercadoPagoApplicationTypeEnum::PAID_ONLINE)
            );
        });

        $data = [
            'stores' => $stores,
            'googleMapsApiKey' => config('services.google.maps_api_key')
        ];

        return view('integrations.index', $data);
    }



    public function toggleEcommerce(Request $request, $id)
    {
        try {
            $store = $this->storeRepository->find($id);
            $store->ecommerce = $request->input('ecommerce');
            $store->save();

            return response()->json([
                'success' => true,
                'message' => 'E-commerce status actualizado exitosamente'
            ]);
        } catch (\Exception $e) {
            Log::error('Error toggling ecommerce status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error actualizando el status del e-commerce'
            ], 500);
        }
    }

    public function saveEmailConfig(Request $request, $storeId)
    {
        try {
            $store = $this->storeRepository->find($storeId);
            $emailConfigController = app(StoresEmailConfigController::class);

            if (!$request->boolean('stores_email_config')) {
                if ($store->emailConfig) {
                    $store->emailConfig->delete();
                }
                return response()->json([
                    'success' => true,
                    'message' => 'Configuración de correo desactivada'
                ]);
            }

            $validator = validator($request->all(), (new StoreEmailConfigRequest())->rules());

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validatedData = $validator->validated();
            return $emailConfigController->storeOrUpdate($validatedData, $storeId);
        } catch (\Exception $e) {
            Log::error('Error saving email config: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error actualizando la configuración de correo'
            ], 500);
        }
    }

    public function handlePedidosYaIntegration(Request $request, $storeId)
    {
        try {
            $store = $this->storeRepository->find($storeId);

            if (!$store) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tienda no encontrada.'
                ], 404);
            }

            $acceptsPeyaEnvios = $request->input('accepts_peya_envios');

            if ($acceptsPeyaEnvios) {
                $peyaEnviosKey = $request->input('peya_envios_key');

                if (empty($peyaEnviosKey)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'La API Key de Pedidos Ya Envíos es requerida.'
                    ], 422);
                }

                $store->peya_envios_key = $peyaEnviosKey;
            } else {
                $store->peya_envios_key = null;
            }

            $store->accepts_peya_envios = $acceptsPeyaEnvios;
            $store->save();

            return response()->json([
                'success' => true,
                'message' => $acceptsPeyaEnvios ? 'Pedidos Ya Envíos activado exitosamente.' : 'Pedidos Ya Envíos desactivado exitosamente.'
            ]);
        } catch (\Exception $e) {
            Log::error('Error toggling Pedidos Ya Envíos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error actualizando la integración de Pedidos Ya Envíos.'
            ], 500);
        }
    }

    public function saveMercadoPagoPresencial(Request $request, $storeId)
    {
        try {
            $store = $this->storeRepository->find($storeId);
            return $this->handleMercadoPagoIntegrationPresencial($request, $store);
        } catch (\Exception $e) {
            Log::error('Error updating MercadoPago Presencial integration: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error actualizando la integración de MercadoPago Presencial: ' . $e->getMessage()
            ], 500);
        }
    }

    public function handleMercadoPagoIntegrationPresencial(Request $request, $store)
    {
        DB::beginTransaction();
        try {

            if (!$request->boolean('accepts_mercadopago_presencial')) {
                $this->mpService->setCredentials($store->id, MercadoPagoApplicationTypeEnum::PAID_PRESENCIAL->value);
                // Eliminar la sucursal de MercadoPago si existe
                $mercadoPagoAccountStore = $this->mercadoPagoAccountStoreRepository->getStoreByExternalId($store->id);
                if (!$mercadoPagoAccountStore) {
                    DB::commit();
                    return response()->json([
                        'success' => true,
                        'message' => 'Integración de MercadoPago Presencial desactivada exitosamente'
                    ]);
                }
                $mercadoPagoAccountStore->load('mercadopagoAccountPOS');
                // Eliminar POS de MercadoPago si existe
                foreach ($mercadoPagoAccountStore->mercadopagoAccountPOS as $pos) {
                    $this->mpService->deletePOS($pos->id_pos);
                    $pos->delete();
                }
                if ($mercadoPagoAccountStore) {
                    $this->mpService->deleteStore($mercadoPagoAccountStore->store_id);
                    $mercadoPagoAccountStore->delete();
                }

                $store->mercadoPagoAccount()->where('type', MercadoPagoApplicationTypeEnum::PAID_PRESENCIAL)->delete();

                DB::commit();
                return response()->json([
                    'success' => true,
                    'message' => 'Integración de MercadoPago Presencial desactivada exitosamente'
                ]);
            }
            $mercadoPagoAccount = $store->mercadoPagoAccount()->updateOrCreate(
                ['store_id' => $store->id, 'type' => MercadoPagoApplicationTypeEnum::PAID_PRESENCIAL],
                [
                    'public_key' => $request->input('mercadoPagoPublicKeyPresencial'),
                    'access_token' => $request->input('mercadoPagoAccessTokenPresencial'),
                    'secret_key' => $request->input('mercadoPagoSecretKeyPresencial'),
                    'user_id_mp' => $request->input('mercadoPagoUserIdPresencial'),
                ]
            );
            $this->mpService->setCredentials($store->id, MercadoPagoApplicationTypeEnum::PAID_PRESENCIAL->value);

            $name = $store->name;
            $externalId = 'SUC' . $store->id;
            $streetNumber = $request->input('street_number');
            $streetName = $request->input('street_name');
            $cityName = $request->input('city_name');
            $stateName = $request->input('state_name');
            $latitude = (float) $request->input('latitude');
            $longitude = (float) $request->input('longitude');
            $reference = $request->input('reference');

            // Verificar si la sucursal ya existe
            $mercadoPagoAccountStoreExist = $this->mercadoPagoAccountStoreRepository->getStoreByExternalId($store->id);

            // Preparar datos de la sucursal
            $storeData = [
                'name' => $name,
                'external_id' => $externalId,
                'location' => [
                    'street_number' => $streetNumber,
                    'street_name' => $streetName,
                    'city_name' => $cityName,
                    'state_name' => $stateName,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'reference' => $reference,
                ],
            ];
            if (!$mercadoPagoAccountStoreExist) {
                $resultMercadoPagoStore = $this->mpService->createStore($storeData);

                $this->mercadoPagoAccountStoreRepository->store([
                    'name' => $name,
                    'external_id' => $externalId,
                    'street_number' => $streetNumber,
                    'street_name' => $streetName,
                    'city_name' => $cityName,
                    'state_name' => $stateName,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'reference' => $reference,
                    'store_id' => $resultMercadoPagoStore['id'],
                    'mercado_pago_account_id' => $mercadoPagoAccount->id,
                ]);
            } else {
                unset($storeData['external_id']);
                $resultMercadoPagoStore = $this->mpService->updateStore($mercadoPagoAccountStoreExist->store_id, $storeData);
                $this->mercadoPagoAccountStoreRepository->update($mercadoPagoAccountStoreExist, [
                    'name' => $name,
                    'street_number' => $streetNumber,
                    'street_name' => $streetName,
                    'city_name' => $cityName,
                    'state_name' => $stateName,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'reference' => $reference,
                    'store_id' => $resultMercadoPagoStore['id'],
                    'mercado_pago_account_id' => $mercadoPagoAccount->id,
                ]);
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Configuración de MercadoPago Presencial actualizada exitosamente'
            ]);
        } catch (MercadoPagoException $e) {
            DB::rollBack();
            Log::channel('mercadopago')->error('Error en la integración con MercadoPago: ' . $e->getMessage());
            // Si es un error de MercadoPago, lanzar una excepción específica
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }catch (\Exception $e) {
            DB::rollBack();
            // Si no es un error de MercadoPago, lanzar una excepción genérica
            Log::channel('mercadopago')->error('Error en la integración con MercadoPago: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error en la integración con MercadoPago.'
            ], 500);
        }
    }

    public function saveMercadoPagoOnline(Request $request, $storeId)
    {
        try {
            $store = $this->storeRepository->find($storeId);
            return $this->handleMercadoPagoIntegrationOnline($request, $store);
        } catch (\Exception $e) {
            Log::error('Error updating MercadoPago Online integration: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error actualizando la integración de MercadoPago Online: ' . $e->getMessage()
            ], 500);
        }
    }

    public function handleMercadoPagoIntegrationOnline(Request $request, $store)
    {
        try {
            DB::beginTransaction();

            if ($request->has('mercadoPagoPublicKeyOnline')) {
                $validator = Validator::make($request->all(), [
                    'mercadoPagoPublicKeyOnline' => 'required',
                    'mercadoPagoAccessTokenOnline' => 'required',
                    'mercadoPagoSecretKeyOnline' => 'required'
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Error de validación',
                        'errors' => $validator->errors()
                    ], 422);
                }

                $store->mercadoPagoAccount()->updateOrCreate(
                    [
                        'store_id' => $store->id,
                        'type' => MercadoPagoApplicationTypeEnum::PAID_ONLINE
                    ],
                    [
                        'public_key' => $request->mercadoPagoPublicKeyOnline,
                        'access_token' => $request->mercadoPagoAccessTokenOnline,
                        'secret_key' => $request->mercadoPagoSecretKeyOnline
                    ]
                );

                DB::commit();
                return response()->json([
                    'success' => true,
                    'message' => 'Configuración de MercadoPago Online actualizada exitosamente'
                ]);
            }

            $store->mercadoPagoAccount()
                ->where('type', MercadoPagoApplicationTypeEnum::PAID_ONLINE)
                ->delete();

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Integración de MercadoPago Online desactivada exitosamente'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en handleMercadoPagoIntegrationOnline: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la integración de MercadoPago Online'
            ], 500);
        }
    }



    public function handlePymoIntegration(Request $request, $storeId)
    {
        try {
            $store = $this->storeRepository->find($storeId);

            if ($request->boolean('invoices_enabled')) {
                $updateData = [
                    'invoices_enabled' => true,
                    'pymo_user' => $request->input('pymo_user'),
                    'pymo_branch_office' => $request->input('pymo_branch_office'),
                ];

                // Only encrypt password if provided
                if ($request->filled('pymo_password')) {
                    $updateData['pymo_password'] = Crypt::encryptString($request->input('pymo_password'));
                }

                if ($request->boolean('automatic_billing')) {
                    $updateData['automatic_billing'] = true;
                } else {
                    $updateData['automatic_billing'] = false;
                }

                $store->update($updateData);

                return response()->json([
                    'success' => true,
                    'message' => 'Configuración de Pymo actualizada exitosamente'
                ]);
            } else {
                $store->update([
                    'invoices_enabled' => false,
                    'pymo_user' => null,
                    'pymo_password' => null,
                    'pymo_branch_office' => null,
                    'automatic_billing' => false,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Integración de Pymo desactivada exitosamente'
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error updating Pymo integration: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error actualizando la integración de Pymo'
            ], 500);
        }
    }

    public function checkPymoConnection($storeId)
    {
        try {
            $store = $this->storeRepository->find($storeId);

            // Verificar credenciales PyMO
            if (!$store->pymo_user || !$store->pymo_password || !$store->invoices_enabled) {
                return response()->json([
                    'success' => false,
                    'message' => 'La tienda no tiene configurada la integración'
                ]);
            }

            // Obtener información de la compañía usando AccountingRepository
            $companyInfo = $this->accountingRepository->getCompanyInfo($store);

            if (!$companyInfo) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo establecer conexión'
                ]);
            }

            // Buscar la sucursal seleccionada
            $selectedBranch = null;
            if ($store->pymo_branch_office) {
                $selectedBranch = collect($companyInfo['branchOffices'])->firstWhere('number', $store->pymo_branch_office);
            }

            Log::info($companyInfo['branchOffices']);
            return response()->json([
                'success' => true,
                'data' => [
                    'name' => $companyInfo['name'] ?? '',
                    'rut' => $companyInfo['rut'] ?? '',
                    'email' => $companyInfo['email'] ?? '',
                    'branchOffices' => $companyInfo['branchOffices'] ?? [],
                    'selectedBranch' => $selectedBranch
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error checking PyMO connection: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al verificar la conexión con PyMO'
            ], 500);
        }
    }

        /**
     * Maneja la integración con OCA
     *
     * @param Request $request
     * @param int $storeId
     * @return \Illuminate\Http\JsonResponse
    */
    public function handleOcaIntegration(Request $request, $storeId)
    {
        try {
            $store = $this->storeRepository->find($storeId);

            if (!$store) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tienda no encontrada.'
                ], 404);
            }

            $acceptsOca = $request->boolean('accepts_oca');

            \DB::transaction(function () use ($store, $acceptsOca, $request) {
                if ($acceptsOca) {
                    $validatedData = $request->validate([
                        'system_id' => 'required|string|max:255',
                        'branch' => 'required|string|max:255',
                    ]);

                    // Eliminar todas las vinculaciones que no sean de OCA en las cajas registradoras de esta tienda
                    $store->cashRegisters()->each(function ($cashRegister) {
                        $cashRegister->posDevices()->where('pos_provider_id', '!=', 4)->detach();
                    });

                    // Eliminar cualquier entrada previa para esta tienda
                    $store->posIntegrationInfo()->delete();

                    // Crear una nueva entrada
                    $store->posIntegrationInfo()->create([
                        'store_id' => $store->id,
                        'pos_provider_id' => 4,
                        'system_id' => $validatedData['system_id'],
                        'branch' => $validatedData['branch'],
                        'company' => null
                    ]);

                    // Actualizar el pos_provider_id en la tabla stores
                    $store->update(['pos_provider_id' => 4]);
                } else {
                    // Eliminar la entrada de pos_integrations_store_info
                    $store->posIntegrationInfo()->where('pos_provider_id', 4)->delete();

                    // Limpiar todas las vinculaciones con dispositivos OCA en las cajas registradoras de esta tienda
                    $store->cashRegisters()->each(function ($cashRegister) {
                        $cashRegister->posDevices()->where('pos_provider_id', 4)->detach();
                    });

                    // Actualizar pos_provider_id a null en la tabla stores
                    $store->update(['pos_provider_id' => null]);
                }
            });

            return response()->json([
                'success' => true,
                'message' => $acceptsOca ? 'OCA activado con éxito.' : 'OCA desactivado con éxito.'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al manejar la integración con OCA: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al procesar la integración con OCA.'
            ], 500);
        }
    }


    /**
     * Maneja la integración con Handy
     *
     * @param Request $request
     * @param int $storeId
     * @return \Illuminate\Http\JsonResponse
    */
    public function handleHandyIntegration(Request $request, $storeId)
    {
        try {
            $store = $this->storeRepository->find($storeId);

            if (!$store) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tienda no encontrada.'
                ], 404);
            }

            $acceptsHandy = $request->boolean('accepts_handy');

            \DB::transaction(function () use ($store, $acceptsHandy, $request) {
                if ($acceptsHandy) {
                    $validatedData = $request->validate([
                        'system_id' => 'required|string|max:255',
                        'branch' => 'required|string|max:255',
                    ]);

                    // Eliminar todas las vinculaciones que no sean de Handy
                    $store->cashRegisters()->each(function ($cashRegister) {
                        $cashRegister->posDevices()->where('pos_provider_id', '!=', 3)->detach();
                    });

                    // Eliminar cualquier entrada previa para esta tienda
                    $store->posIntegrationInfo()->delete();

                    // Crear una nueva entrada
                    $store->posIntegrationInfo()->create([
                        'store_id' => $store->id,
                        'pos_provider_id' => 3,
                        'system_id' => $validatedData['system_id'],
                        'branch' => $validatedData['branch'],
                        'company' => null
                    ]);

                    // Actualizar el pos_provider_id en la tabla stores
                    $store->update(['pos_provider_id' => 3]);
                } else {
                    // Eliminar la entrada de pos_integrations_store_info
                    $store->posIntegrationInfo()->where('pos_provider_id', 3)->delete();

                    // Limpiar todas las vinculaciones de dispositivos Handy
                    $store->cashRegisters()->each(function ($cashRegister) {
                        $cashRegister->posDevices()->where('pos_provider_id', 3)->detach();
                    });

                    // Actualizar pos_provider_id a null
                    $store->update(['pos_provider_id' => null]);
                }
            });

            return response()->json([
                'success' => true,
                'message' => $acceptsHandy ? 'Handy activado con éxito.' : 'Handy desactivado con éxito.'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al manejar la integración con Handy: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al procesar la integración con Handy.'
            ], 500);
        }
    }



    /**
     * Maneja la integración con Fiserv
     *
     * @param Request $request
     * @param int $storeId
     * @return \Illuminate\Http\JsonResponse
    */
    public function handleFiservIntegration(Request $request, $storeId)
    {
        try {
            $store = $this->storeRepository->find($storeId);

            if (!$store) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tienda no encontrada.'
                ], 404);
            }

            $acceptsFiserv = $request->boolean('accepts_fiserv');

            \DB::transaction(function () use ($store, $acceptsFiserv, $request) {
                if ($acceptsFiserv) {
                    $validatedData = $request->validate([
                        'system_id' => 'required|string|max:255',
                        'branch' => 'required|string|max:255',
                    ]);

                    // Eliminar todas las vinculaciones que no sean de Fiserv
                    $store->cashRegisters()->each(function ($cashRegister) {
                        $cashRegister->posDevices()->where('pos_provider_id', '!=', 2)->detach();
                    });

                    // Eliminar cualquier entrada previa para esta tienda
                    $store->posIntegrationInfo()->delete();

                    // Crear una nueva entrada
                    $store->posIntegrationInfo()->create([
                        'store_id' => $store->id,
                        'pos_provider_id' => 2,
                        'system_id' => $validatedData['system_id'],
                        'branch' => $validatedData['branch'],
                        'company' => null
                    ]);

                    // Actualizar el pos_provider_id en la tabla stores
                    $store->update(['pos_provider_id' => 2]);
                } else {
                    // Eliminar la entrada de pos_integrations_store_info
                    $store->posIntegrationInfo()->where('pos_provider_id', 2)->delete();

                    // Limpiar todas las vinculaciones de dispositivos Fiserv
                    $store->cashRegisters()->each(function ($cashRegister) {
                        $cashRegister->posDevices()->where('pos_provider_id', 2)->detach();
                    });

                    // Actualizar pos_provider_id a null
                    $store->update(['pos_provider_id' => null]);
                }
            });

            return response()->json([
                'success' => true,
                'message' => $acceptsFiserv ? 'Fiserv activado con éxito.' : 'Fiserv desactivado con éxito.'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al manejar la integración con Fiserv: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al procesar la integración con Fiserv.'
            ], 500);
        }
    }


    /**
     * Maneja la integración con Scanntech
     *
     * @param Request $request
     * @param int $storeId
     * @return \Illuminate\Http\JsonResponse
    */
    public function handleScanntechIntegration(Request $request, $storeId)
    {
        try {
            $store = $this->storeRepository->find($storeId);

            if (!$store) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tienda no encontrada.'
                ], 404);
            }

            $acceptsScanntech = $request->boolean('accepts_scanntech');

            \DB::transaction(function () use ($store, $acceptsScanntech, $request) {
                if ($acceptsScanntech) {
                    $validatedData = $request->validate([
                        'branch' => 'required|string|max:255',
                        'company' => 'required|string|max:255'
                    ]);

                    // Eliminar todas las vinculaciones que no sean de Scanntech
                    $store->cashRegisters()->each(function ($cashRegister) {
                        $cashRegister->posDevices()->where('pos_provider_id', '!=', 1)->detach();
                    });

                    // Eliminar cualquier entrada previa para esta tienda
                    $store->posIntegrationInfo()->delete();

                    // Crear una nueva entrada
                    $store->posIntegrationInfo()->create([
                        'store_id' => $store->id,
                        'pos_provider_id' => 1,
                        'system_id' => null, // Scanntech no requiere system_id
                        'branch' => $validatedData['branch'],
                        'company' => $validatedData['company'],
                    ]);

                    // Actualizar el pos_provider_id en la tabla stores
                    $store->update(['pos_provider_id' => 1]);
                } else {
                    // Eliminar la entrada de pos_integrations_store_info
                    $store->posIntegrationInfo()->where('pos_provider_id', 1)->delete();

                    // Limpiar todas las vinculaciones de dispositivos Scanntech
                    $store->cashRegisters()->each(function ($cashRegister) {
                        $cashRegister->posDevices()->where('pos_provider_id', 1)->detach();
                    });

                    // Actualizar pos_provider_id a null
                    $store->update(['pos_provider_id' => null]);
                }
            });

            return response()->json([
                'success' => true,
                'message' => $acceptsScanntech ? 'Scanntech activado con éxito.' : 'Scanntech desactivado con éxito.'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al manejar la integración con Scanntech: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al procesar la integración con Scanntech.'
            ], 500);
        }
    }




    public function create()
    {
        return;
    }


    public function store()
    {
        return;
    }

    public function show($id)
    {
        return;
    }

    public function edit($id)
    {
        return;
    }

    public function update()
    {
        return;
    }

    public function destroy($id)
    {
        return;
    }

    /**
     * Verifica la conexión con MercadoPago Presencial
     *
     * @param int $storeId
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkMercadoPagoPresencialConnection($storeId)
    {
        try {
            $store = $this->storeRepository->find($storeId);
            if (!$store) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tienda no encontrada.'
                ], 404);
            }

            $mercadoPagoAccount = $store->mercadoPagoAccount->firstWhere('type', MercadoPagoApplicationTypeEnum::PAID_PRESENCIAL);
            $mercadoPagoAccount->load('mercadoPagoAccountStore');
            if (!$mercadoPagoAccount) {
                return response()->json([
                    'success' => false,
                    'message' => 'La tienda no tiene configurada la integración de MercadoPago Presencial.'
                ]);
            }
            $storeData = [
                'public_key' => $mercadoPagoAccount->public_key,
                'access_token' => $mercadoPagoAccount->access_token,
                'secret_key' => $mercadoPagoAccount->secret_key,
                'user_id_mp' => $mercadoPagoAccount->user_id_mp,
                'name' => $mercadoPagoAccount->mercadoPagoAccountStore[0]?->name,
                'external_id' => 'SUC' . $mercadoPagoAccount->mercadoPagoAccountStore[0]?->external_id,
                'location' => [
                    'street_number' => $mercadoPagoAccount->mercadoPagoAccountStore[0]?->street_number,
                    'street_name' => $mercadoPagoAccount->mercadoPagoAccountStore[0]?->street_name,
                    'city_name' => $mercadoPagoAccount->mercadoPagoAccountStore[0]?->city_name,
                    'state_name' => $mercadoPagoAccount->mercadoPagoAccountStore[0]?->state_name,
                    'latitude' => $mercadoPagoAccount->mercadoPagoAccountStore[0]?->latitude,
                    'longitude' => $mercadoPagoAccount->mercadoPagoAccountStore[0]?->longitude,
                    'reference' => $mercadoPagoAccount->mercadoPagoAccountStore[0]?->reference,
                ],
            ];

            return response()->json([
                'success' => true,
                'data' => $storeData
            ]);

        } catch (MercadoPagoException $e) {
            Log::channel('mercadopago')->error('Error en la integración con MercadoPago: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            Log::channel('mercadopago')->error('Error en la integración con MercadoPago: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error en la integración con MercadoPago.'
            ], 500);
        }

    }
}
