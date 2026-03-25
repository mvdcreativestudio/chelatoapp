<?php

namespace App\Http\Controllers;

use App\Repositories\AccountingRepository;
use App\Http\Requests\SaveRutRequest;
use App\Http\Requests\UploadLogoRequest;
use Yajra\DataTables\DataTables;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\EmitNoteRequest;
use Illuminate\Support\Facades\Log;
use App\Models\CFE;
use App\Models\Store;
use App\Services\Billing\BillingServiceResolver;
use App\Repositories\SicfeRepository;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class AccountingController extends Controller
{
    /**
     * Repositorio de contabilidad.
     *
     * @var AccountingRepository
    */
    protected $accountingRepository;

    /**
     * Constructor para inyectar el repositorio en el controlador.
     *
     * @param AccountingRepository $accountingRepository
    */
    public function __construct(AccountingRepository $accountingRepository)
    {
        $this->accountingRepository = $accountingRepository;
    }

    /**
     * Muestra la vista de recibos.
     *
     * @return View
    */
    public function receipts(): View
    {
        return view('content.accounting.receipts');
    }

    /**
     * Muestra la vista de entradas contables.
     *
     * @return View
    */
    public function entries(): View
    {
        return view('content.accounting.entries.index');
    }

    /**
     * Muestra la vista de una entrada contable específica.
     *
     * @return View
    */
    public function entrie(): View
    {
        return view('content.accounting.entries.entry-details.index');
    }

    /**
     * Muestra las estadísticas de los CFEs enviados.
     *
     * @return View
    */
    public function getSentCfes(): View
    {
        $statistics = $this->accountingRepository->getDashboardStatistics();
        return view('content.accounting.invoices.index', $statistics);
    }

    /**
     * Obtiene los datos para la tabla de recibos en formato JSON.
     *
     * @return JsonResponse
    */
    public function getInvoicesData(): JsonResponse
    {
        $invoicesData = $this->accountingRepository->getInvoicesDataForDatatables();
        return DataTables::of($invoicesData)->make(true);
    }

    /**
     * Muestra la configuración de la contabilidad.
     *
     * @return View
    */
    public function settings(): View
    {
        $pymoSetting = $this->accountingRepository->getRutSetting();
        $companyInfo = null;
        $logoUrl = null;

        if ($pymoSetting) {
            $rut = $pymoSetting->settingValue;
            $companyInfo = $this->accountingRepository->getCompanyInfo($rut);
            $logoUrl = $this->accountingRepository->getCompanyLogo($rut);
        }

        return view('content.accounting.settings', compact('pymoSetting', 'companyInfo', 'logoUrl'));
    }

    /**
     * Guarda el RUT de la empresa.
     *
     * @param SaveRutRequest $request
     * @return RedirectResponse
    */
    public function saveRut(SaveRutRequest $request): RedirectResponse
    {
        $this->accountingRepository->saveRut($request->rut);
        return redirect()->back()->with('success_rut', 'RUT guardado correctamente.');
    }

    /**
     * Sube el logo de la empresa.
     *
     * @param UploadLogoRequest $request
     * @return RedirectResponse
    */
    public function uploadLogo(UploadLogoRequest $request): RedirectResponse
    {
        if ($this->accountingRepository->uploadCompanyLogo($request->store_id, $request->file('logo'))) {
            return redirect()->back()->with('success_logo', 'Logo actualizado correctamente.');
        }

        return redirect()->back()->with('error_logo', 'Error al actualizar el logo.');
    }

    /**
     * Maneja la emisión de notas de crédito o débito.
     *
     * @param EmitNoteRequest $request
     * @param int $invoiceId
     * @return RedirectResponse|JsonResponse
     */
    public function emitNote(EmitNoteRequest $request, int $invoiceId): RedirectResponse|JsonResponse
    {
        try {
            // Buscar el CFE original y resolver el servicio de facturación
            $cfe = CFE::with('store.billingProvider')->findOrFail($invoiceId);
            $store = $cfe->order?->store ?? $cfe->store;

            if (!$store) {
                throw new \Exception('No se encontró la tienda para este CFE.');
            }

            $billingService = app(BillingServiceResolver::class)->resolve($store);
            $billingService->emitNote($invoiceId, $request);

            Log::info("Nota emitida correctamente para la factura {$invoiceId}");

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Nota emitida correctamente.']);
            }

            return redirect()->back()->with('success', 'Nota emitida correctamente.');
        } catch (\Throwable $e) {
            Log::error('Error al emitir nota:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'invoice_id' => $invoiceId,
                'request_data' => $request->all(),
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ocurrió un error al emitir la nota.',
                    'error' => $e->getMessage()
                ], 500);
            }

            return redirect()->back()->withErrors(['error' => 'Ocurrió un error al emitir la nota.']);
        }
    }

    /**
     * Descarga el PDF de un CFE (PyMo o SICFE según el proveedor de la tienda).
     *
     * @param int|string $cfeId
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\Response
     */
    public function downloadCfePdf($cfeId)
    {
        try {
            $cfe = CFE::query()
                ->with(['order.store.billingProvider', 'store.billingProvider'])
                ->findOrFail($cfeId);

            $store = $cfe->order?->store ?? $cfe->store;
            if (! $store) {
                abort(500, 'No se pudo determinar la tienda del CFE.');
            }

            return app(BillingServiceResolver::class)->resolve($store)->getCfePdf((int) $cfeId);
        } catch (\Throwable $e) {
            Log::error('Error al descargar PDF del CFE.', [
                'cfe_id' => $cfeId,
                'error' => $e->getMessage(),
            ]);

            // target="_blank" no tiene "back"; evitar redirect que deja la pestaña vacía
            return response($e->getMessage(), 500)->header('Content-Type', 'text/plain; charset=UTF-8');
        }
    }

    /**
     * Genera e imprime el ticket 80mm de un CFE.
     * Usa business_name (razón social) del store para el encabezado fiscal.
     *
     * @param int|string $cfeId
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\Response
     */
    public function printCfePdf($cfeId)
    {
        try {
            $cfe = CFE::query()
                ->with(['order.client', 'order.store', 'store', 'mainCfe'])
                ->findOrFail($cfeId);

            $order = $cfe->order;
            $store = $order?->store ?? $cfe->store;

            if (!$store) {
                abort(500, 'No se pudo determinar la tienda del CFE.');
            }

            // Obtener logo desde PyMo o local
            $logo = null;
            try {
                $logo = $this->accountingRepository->getCompanyLogo($store);
            } catch (\Exception $e) {
                // Silenciar
            }
            if (!$logo && $store->logo) {
                $logoPath = storage_path('app/public/' . $store->logo);
                if (file_exists($logoPath)) {
                    $logo = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
                }
            }

            $html = view('invoices.pdf.cfe_80mm', [
                'cfe'   => $cfe,
                'order' => $order,
                'store' => $store,
                'logo'  => $logo,
            ])->render();

            $paperWidth = 204.094; // 72mm en puntos

            $pdf = Pdf::loadHTML($html)
                ->setPaper([0, 0, $paperWidth, 1000], 'portrait')
                ->setOption('isHtml5ParserEnabled', true)
                ->setOption('isRemoteEnabled', true)
                ->setOption('defaultPaperSize', 'custom')
                ->setOption('enable_auto_height', true)
                ->setOption('margin-top', 0)
                ->setOption('margin-bottom', 0)
                ->setOption('margin-left', 0)
                ->setOption('margin-right', 0);

            return $pdf->stream("ticket_{$cfe->id}.pdf");
        } catch (\Throwable $e) {
            Log::error('Error al imprimir PDF 80mm del CFE.', [
                'cfe_id' => $cfeId,
                'error'  => $e->getMessage(),
            ]);

            return response($e->getMessage(), 500)->header('Content-Type', 'text/plain; charset=UTF-8');
        }
    }

    /**
     * Maneja la emisión de un recibo sobre una factura o eTicket existente.
     *
     * @param int $invoiceId
     * @return RedirectResponse
    */
    public function emitReceipt(int $invoiceId): RedirectResponse
    {
        try {
            $this->accountingRepository->emitReceipt($invoiceId);
            Log::info("Recibo emitido correctamente para la factura {$invoiceId}");
            return redirect()->back()->with('success', 'Recibo emitido correctamente.');
        } catch (\Exception $e) {
            Log::error("Error al emitir recibo para la factura {$invoiceId}: {$e->getMessage()}");
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
      * Maneja la llegada de un webhook de PyMo.
      *
      * @param Request $request
      * @return void
    */
    public function webhook(Request $request): void
    {
        $data = $request->all(); // Obtener los datos del webhook

        Log::info('Recibiendo webhook');

        $type = $data['type']; // Obtener el tipo de webhook
        $urlToCheck = $data['url_to_check']; // Obtener la URL a la que hacer la petición

        switch ($type) { // Según el tipo de webhook
            case 'CFE_STATUS_CHANGE': // Si es un cambio de estado de CFE
                // Extraer el RUT y la sucursal usando expresiones regulares
                preg_match('/\/companies\/(\d+)\/sentCfes\/(\d+)/', $urlToCheck, $matches);

                if (isset($matches[1]) && isset($matches[2])) {
                    $rut = $matches[1];         // Primer grupo de captura es el RUT
                    $branchOffice = $matches[2]; // Segundo grupo de captura es la sucursal

                    Log::info('Rut de la empresa Webhook: ' . $rut);
                    Log::info('Branch Office Webhook: ' . $branchOffice);

                    $this->accountingRepository->checkCfeStatus($rut, $branchOffice, $urlToCheck);
                } else {
                    Log::info('No se pudieron extraer el RUT y la sucursal de la URL.');
                }
                break;

            default:
                Log::info('Invalid request');
                return;
        }
    }

    /**
     * Actualiza el estado de todos los CFEs para la empresa del usuario autenticado.
     * Detecta automáticamente el proveedor (PyMo o SICFE) y consulta el estado correspondiente.
     *
     * @return JsonResponse
    */
    public function updateAllCfesStatus(): JsonResponse
    {
        try {
            $store = auth()->user()->store;

            if (!$store) {
                return response()->json(['error' => 'No se encontró la empresa para el usuario autenticado.'], 404);
            }

            $store->loadMissing('billingProvider');
            $providerCode = strtolower($store->billingProvider->code ?? '');

            if ($providerCode === 'sicfe') {
                $sicfeRepo = app(SicfeRepository::class);
                $updated = $sicfeRepo->updateCfeStatuses($store);
                return response()->json(['success' => "Estados actualizados correctamente. {$updated} CFE(s) actualizados vía SICFE."]);
            } else {
                $this->accountingRepository->updateAllCfesForStore($store);
                return response()->json(['success' => 'Los estados de los CFEs se han actualizado correctamente.']);
            }
        } catch (\Exception $e) {
            Log::error('Excepción al actualizar los CFEs: ' . $e->getMessage());
            return response()->json(['error' => 'Ocurrió un error al actualizar los CFEs: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Actualiza el estado de todos los CFE's para todas las empresas.
     *
     * @return JsonResponse
    */
    public function updateAllCfesStatusForAllStores(): JsonResponse
    {
        try {
            $this->accountingRepository->updateAllCfesStatusForAllStores();

            return response()->json(['success' => 'Los estados de los CFEs se han actualizado correctamente.']);
        } catch (\Exception $e) {
            Log::error('Excepción al actualizar los CFEs: ' . $e->getMessage());
            return response()->json(['error' => 'Ocurrió un error al actualizar los CFEs.'], 500);
        }
    }

    /**
     * Muestra la vista de CFEs recibidos.
     * Detecta automáticamente el proveedor (PyMo o SICFE).
     *
     * @return RedirectResponse | View
    */
    public function receivedCfes(): RedirectResponse | View
    {
        $store = auth()->user()->store;

        if (!$store) {
            return redirect()->back()->with('error', 'No se encontró la empresa para el usuario autenticado.');
        }

        try {
            $store->loadMissing('billingProvider');
            $providerCode = strtolower($store->billingProvider->code ?? '');

            if ($providerCode === 'sicfe') {
                $sicfeRepo = app(SicfeRepository::class);
                $cfes = $sicfeRepo->processReceivedCfes($store);
            } else {
                $cfes = $this->accountingRepository->processReceivedCfes($store);
            }

            if ($cfes === null) {
                return redirect()->back()->with('error', 'Error al consultar CFEs recibidos.');
            }

            return view('content.accounting.received_cfes', compact('cfes'));
        } catch (\Exception $e) {
            Log::error('Error al obtener los CFE recibidos: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al obtener los CFE recibidos: ' . $e->getMessage());
        }
    }

    /**
     * Obtiene los datos de los CFEs recibidos para la tabla en formato JSON.
     *
     * @return JsonResponse
    */
    public function getReceivedCfesData(): JsonResponse
    {
        try {
            // Obtener la empresa del usuario autenticado
            $store = auth()->user()->store;

            // Obtener los datos formateados para la DataTable
            $receivedCfesData = $this->accountingRepository->getReceivedCfesDataForDatatables($store);

            // Retornar la respuesta en formato JSON para la DataTable
            return DataTables::of($receivedCfesData)->make(true);
        } catch (\Exception $e) {
            Log::error('Error al obtener los datos de los CFEs recibidos para la DataTable: ' . $e->getMessage());
            return response()->json(['error' => 'Ocurrió un error al obtener los datos de los CFEs recibidos.'], 500);
        }
  }
}
