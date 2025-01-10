<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Repositories\StoreRepository;
use App\Http\Requests\StoreEmailConfigRequest;
use App\Enums\MercadoPago\MercadoPagoApplicationTypeEnum;
use App\Http\Controllers\StoresEmailConfigController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use App\Repositories\AccountingRepository;

class IntegrationController extends Controller
{
    protected $storeRepository;
    protected $accountingRepository;

    public function __construct(StoreRepository $storeRepository, AccountingRepository $accountingRepository)
    {
        $this->storeRepository = $storeRepository;
        $this->accountingRepository = $accountingRepository;
    }

    public function index()
    {
        $stores = $this->storeRepository->getAll()->load([
            'mercadoPagoAccount',
            'mercadoPagoAccountStore'
        ]);

        $stores = $stores->map(function ($store) {
            if ($store->invoices_enabled && $store->pymo_user && $store->pymo_password) {
                $companyInfo = $this->accountingRepository->getCompanyInfo($store);
                $store->pymoInfo = $companyInfo;
                $store->branchOffices = $companyInfo['branchOffices'] ?? [];
            }

            return $store->setAttribute(
                'mercadoPagoOnline',
                $store->mercadoPagoAccount->firstWhere('type', MercadoPagoApplicationTypeEnum::PAID_ONLINE)
            );
        });
        return view('integrations.index', compact('stores'));
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
        try {
            DB::beginTransaction();

            if ($request->has('mercadoPagoPublicKeyPresencial')) {
                $validator = Validator::make($request->all(), [
                    'mercadoPagoPublicKeyPresencial' => 'required',
                    'mercadoPagoAccessTokenPresencial' => 'required',
                    'mercadoPagoSecretKeyPresencial' => 'required',
                    'mercadoPagoUserIdPresencial' => 'required'
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
                        'type' => MercadoPagoApplicationTypeEnum::PAID_PRESENCIAL
                    ],
                    [
                        'public_key' => $request->mercadoPagoPublicKeyPresencial,
                        'access_token' => $request->mercadoPagoAccessTokenPresencial,
                        'secret_key' => $request->mercadoPagoSecretKeyPresencial,
                        'user_id_mp' => $request->mercadoPagoUserIdPresencial
                    ]
                );

                DB::commit();
                return response()->json([
                    'success' => true,
                    'message' => 'Configuración de MercadoPago Presencial actualizada exitosamente'
                ]);
            }

            $store->mercadoPagoAccount()
                ->where('type', MercadoPagoApplicationTypeEnum::PAID_PRESENCIAL)
                ->delete();

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Integración de MercadoPago Presencial desactivada exitosamente'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en handleMercadoPagoIntegrationPresencial: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la integración de MercadoPago Presencial'
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
                    'message' => 'La tienda no tiene configurada la integración con PyMO'
                ]);
            }

            // Obtener información de la compañía usando AccountingRepository
            $companyInfo = $this->accountingRepository->getCompanyInfo($store);

            if (!$companyInfo) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo establecer conexión con PyMO'
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
}
