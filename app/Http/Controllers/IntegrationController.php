<?php

namespace App\Http\Controllers;

use App\Models\BillingCredential;
use App\Models\BillingProvider;
use App\Models\Store;
use App\Services\Billing\BillingServiceResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class IntegrationController extends Controller
{
    public function handleSicfeIntegration(Request $request, $storeId)
    {
        try {
            $store = Store::findOrFail($storeId);

            if (!$request->boolean('invoices_enabled')) {
                $store->update([
                    'invoices_enabled' => false,
                    'billing_provider_id' => null
                ]);

                $store->billingCredential()?->delete();

                return response()->json([
                    'success' => true,
                    'message' => 'Integración de SICFE desactivada exitosamente.'
                ]);
            }

            $validated = $request->validate([
                'sicfe_user' => 'required|string|max:255',
                'sicfe_password' => 'required|string|max:255',
                'sicfe_branch_office' => 'required|string|max:255',
                'sicfe_tenant' => 'required|string|max:255',
                'has_special_caes' => 'boolean'
            ]);

            BillingCredential::updateOrCreate(
                ['store_id' => $store->id],
                [
                    'user' => $validated['sicfe_user'],
                    'password' => Crypt::encryptString($validated['sicfe_password']),
                    'branch_office' => $validated['sicfe_branch_office'],
                    'tenant' => $validated['sicfe_tenant'],
                    'has_special_caes' => $validated['has_special_caes'] ?? false,
                ]
            );

            $sicfeProvider = BillingProvider::where('code', 'sicfe')->first();
            if (!$sicfeProvider) {
                Log::error('SICFE: no existe el proveedor con code=sicfe en billing_providers.');

                return response()->json([
                    'success' => false,
                    'message' => 'El proveedor SICFE no está registrado. Ejecutá las migraciones y php artisan db:seed --class=BillingProviderSeeder.',
                ], 503);
            }

            $store->update([
                'invoices_enabled' => true,
                'billing_provider_id' => $sicfeProvider->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Integración de SICFE actualizada exitosamente.'
            ]);
        } catch (\Exception $e) {
            Log::error('Error en integración SICFE: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la integración de SICFE.'
            ], 500);
        }
    }

    public function checkSicfeConnection(Store $store)
    {
        $ruc = $store->rut;

        if (!$ruc) {
            return response()->json(['success' => false, 'message' => 'El RUT no está definido en la tienda.']);
        }

        $credentials = $store->billingCredential;

        if (!$credentials) {
            return response()->json(['success' => false, 'message' => 'No se encontraron credenciales de facturación.']);
        }

        try {
            $billingService = app(BillingServiceResolver::class)->resolve($store);
            $data = $billingService->consultarDatosRuc($ruc, $store);

            return response()->json(['success' => true, 'data' => $data['ObtenerDatosRUCDGIResult'] ?? []]);

        } catch (\Exception $e) {
            Log::error('SICFE - Error al procesar conexión:', [
                'store_id' => $store->id,
                'error' => $e->getMessage()
            ]);
            return response()->json(['success' => false, 'message' => 'Error al procesar la conexión con SICFE.']);
        }
    }
}
