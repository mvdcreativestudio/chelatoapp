<?php

namespace App\Http\Controllers;

use App\Helpers\Helpers;
use App\Http\Requests\EmitNoteRequest;
use App\Http\Requests\SaveRutRequest;
use App\Http\Requests\UploadLogoRequest;
use App\Models\CFE;
use App\Models\Store;
use App\Repositories\AccountingRepository;
use App\Repositories\StoresEmailConfigRepository;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Yajra\DataTables\DataTables;
use App\Models\Budget;

class AccountingController extends Controller
{
    /**
     * Repositorio de contabilidad.
     *
     * @var AccountingRepository
     */
    protected $accountingRepository;
    protected $storesEmailConfigRepository;
    /**
     * Constructor para inyectar el repositorio en el controlador.
     *
     * @param AccountingRepository $accountingRepository
     */
    public function __construct(AccountingRepository $accountingRepository, StoresEmailConfigRepository $storesEmailConfigRepository)
    {
        $this->accountingRepository = $accountingRepository;
        $this->storesEmailConfigRepository = $storesEmailConfigRepository;
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
        $isStoreConfigEmailEnabled = $this->storesEmailConfigRepository->getConfigByStoreId(auth()->user()->store_id);
        $mergeData = array_merge($statistics, compact('isStoreConfigEmailEnabled'));
        return view('content.accounting.invoices.index', $mergeData);
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
        if (Auth::user()->can('view_all_stores')) {
            $stores = Store::all(); // Todas las tiendas si tiene permiso
        } else {
            $stores = Store::where('id', Auth::user()->store_id)->get(); // Solo la tienda asignada
        }

        return view('content.accounting.settings', compact('stores'));
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
     * @return RedirectResponse
     */
    public function emitNote(EmitNoteRequest $request, int $invoiceId): RedirectResponse
    {
        try {
            $this->accountingRepository->emitNote($invoiceId, $request);
            Log::info("Nota emitida correctamente para la factura {$invoiceId}");
            return redirect()->back()->with('success', 'Nota emitida correctamente.');
        } catch (\Exception $e) {
            Log::error("Error al emitir nota para la factura {$invoiceId}: {$e->getMessage()}");
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Descarga el PDF de un CFE.
     *
     * @param int $cfeId
     * @return mixed
     */
    public function downloadCfePdf($cfeId)
    {
        try {
            return $this->accountingRepository->getCfePdf($cfeId);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Descarga el PDF de un CFE en 80mm
     *
     * @param $cfeId
     * @return mixed
     */
    public function getCfePdf80mm($cfeId)
    {
        try {
            return $this->accountingRepository->getCfePdf80mm($cfeId);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
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
                    $rut = $matches[1]; // Primer grupo de captura es el RUT
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
     *
     * @return JsonResponse
     */
    public function updateAllCfesStatus(): JsonResponse
    {
        try {
            // Obtener la empresa del usuario autenticado
            $store = auth()->user()->store;

            if (!$store) {
                return response()->json(['error' => 'No se encontró la empresa para el usuario autenticado.'], 404);
            }

            // Llamar al método del repositorio para actualizar los CFEs
            $this->accountingRepository->updateAllCfesForStore($store);

            return response()->json(['success' => 'Los estados de los CFEs se han actualizado correctamente.']);
        } catch (\Exception $e) {
            Log::error('Excepción al actualizar los CFEs: ' . $e->getMessage());
            return response()->json(['error' => 'Ocurrió un error al actualizar los CFEs.'], 500);
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
            $cfes = $this->accountingRepository->processReceivedCfes($store);

            if (!$cfes) {
                return redirect()->back()->with('error', 'No se encontraron CFE recibidos.');
            }

            return view('content.accounting.received_cfes', compact('cfes'));
        } catch (\Exception $e) {
            Log::error('Error al obtener los CFE recibidos: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al obtener los CFE recibidos.');
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

    // sendEmail
    public function sendEmail(Request $request)
    {
        try {

            $invoiceId = $request->invoice_id;
            $email = $request->email;
            $factura = $this->downloadCfePdf($invoiceId);
            $tempPdfPath = tempnam(sys_get_temp_dir(), 'pdf_');
            $subject = 'Factura de compra';
            file_put_contents($tempPdfPath, $factura);
            $attachmentName = "Factura.pdf"; // Asigna el nombre del archivo
            Helpers::emailService()->sendMail($email, $subject, 'content.accounting.invoices.email', $tempPdfPath, $attachmentName);
            return response()->json(['success' => 'Correo enviado correctamente.']);
        } catch (\Exception $e) {
            dd($e);
            Log::error('Error al enviar correo: ' . $e->getMessage());
            return response()->json(['error' => 'Ocurrió un error al enviar el correo.'], 500);
        }
    }

    public function fetchActiveCaesByType(int $storeId, string $type): JsonResponse
    {
        Log::info('Solicitud recibida para un tipo específico', ['storeId' => $storeId, 'type' => $type]);

        $store = Store::find($storeId);

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'No se encontró la tienda especificada.'], 404);
        }

        $cookies = $this->accountingRepository->login($store);

        if (!$cookies) {
            return response()->json(['success' => false, 'message' => 'No se pudo iniciar sesión.'], 500);
        }

        $rut = $store->rut;

        if (!$rut) {
            return response()->json(['success' => false, 'message' => 'El RUT de la tienda no está configurado.'], 400);
        }

        $url = env('PYMO_HOST') . ':' . env('PYMO_PORT') . '/' . env('PYMO_VERSION') . '/companies/' . $rut . '/cfesActiveNumbers/' . $type;

        try {
            Log::info("Consultando URL: {$url}");
            $response = Http::withCookies($cookies, parse_url(env('PYMO_HOST'), PHP_URL_HOST))->get($url);

            if ($response->successful()) {
                Log::info("Respuesta exitosa para el tipo {$type}", $response->json());
                return response()->json(['success' => true, 'data' => $response->json()]);
            } else {
                Log::error("Error en la API para el tipo {$type}: " . $response->status() . ' - ' . $response->body());
                return response()->json(['success' => false, 'message' => 'Error al consultar la API.'], $response->status());
            }
        } catch (\Exception $e) {
            Log::error("Excepción al consultar el tipo {$type}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Ocurrió un error inesperado.'], 500);
        }
    }


    /**
     * Sube un archivo XML con los CAEs nuevos.
     *
     * @param Request $request
     * @param string $rut
     * @param string $type
     * @return JsonResponse
    */
    public function uploadCae(Request $request, $rut, $type)
    {
        $validated = $request->validate([
            'CfesNewNumbers' => 'required|file|mimes:xml|max:2048', // Validación del archivo XML
        ]);

        Log::info('Solicitud para cargar CAE', ['rut' => $rut, 'type' => $type]);

        // Buscar la tienda asociada al RUT
        $store = Store::where('rut', $rut)->first();

        if (!$store) {
            Log::error('No se encontró la tienda para el RUT proporcionado.');
            return response()->json(['status' => 'ERROR', 'message' => 'Tienda no encontrada para el RUT proporcionado.'], 404);
        }

        try {
            // Usar el repositorio para manejar la lógica de carga del CAE
            $response = $this->accountingRepository->uploadCaeToPymo($store, $type, $validated['CfesNewNumbers']);

            if ($response['success']) {
                return response()->json(['status' => 'SUCCESS', 'message' => $response['message']], 200);
            }

            return response()->json(['status' => 'ERROR', 'message' => $response['message']], $response['statusCode'] ?? 500);
        } catch (\Exception $e) {
            Log::error("Error al cargar CAE: {$e->getMessage()}");
            return response()->json(['status' => 'ERROR', 'message' => 'Error inesperado al cargar el CAE.'], 500);
        }
    }

    public function sendBudgetEmail(Request $request)
    {
        try {
            $request->validate([
                'budget_id' => 'required|exists:budgets,id',
                'email' => 'required|email'
            ]);

            $budget = Budget::with(['client', 'lead', 'store', 'items.product'])
                ->findOrFail($request->budget_id);

            $companySettings = \App\Models\CompanySettings::first();

            // Generar PDF
            $pdf = PDF::loadView('budgets.pdf', [
                'budget' => $budget,
                'companySettings' => $companySettings
            ]);

            $tempPdfPath = tempnam(sys_get_temp_dir(), 'pdf_');
            file_put_contents($tempPdfPath, $pdf->output());

            $subject = 'Presupuesto #' . $budget->id;
            $attachmentName = "Presupuesto_{$budget->id}.pdf";

            // Enviar email con los datos correctos
            Helpers::emailService()->sendMail(
                $request->email,
                $subject,
                'content.budgets.email',
                $tempPdfPath,
                $attachmentName,
                ['budget' => $budget] // Asegúrate de que se pase como array asociativo
            );

            @unlink($tempPdfPath);

            return response()->json([
                'success' => true,
                'message' => 'Correo enviado correctamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al enviar correo del presupuesto: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al enviar el correo: ' . $e->getMessage()
            ], 500);
        }
    }



    /**
     * Exporta los CFEs Invoices a PDF.
     *
     * @param Request $request
     * @return mixed
     */

    public function cfesInvoicesExportPDF(Request $request)
    {
        $invoicesData = $this->accountingRepository->getInvoicesDataForDatatables($request);
        $pdf = Pdf::loadView('content.accounting.invoices.pdf', compact('invoicesData'));

        return $pdf->download('facturas-' . date('Y-m-d_H-i-s') . '.pdf');
    }

    /**
     * Exporta los CFEs Invoices a CSV.
     *
     * @param Request $request
     * @return mixed
     */
    public function cfesInvoicesExportCSV(Request $request)
    {
        $invoicesData = $this->accountingRepository->getInvoicesDataForDatatables($request);
        try {
            // Reutilizas la misma clase export, pero specifies CSV writer:
            return Excel::download(new CFEExport($invoicesData), 'facturas-' . date('Y-m-d_H-i-s') . '.csv', \Maatwebsite\Excel\Excel::CSV);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Error al exportar las facturas a CSV. Por favor, intente nuevamente.');
        }
    }


    /**
     * Exporta los CFEs Invoices Receipts a Excel.
     *
     * @param Request $request
     * @return mixed
     */
    public function cfesInvoicesReceiptsExportExcel(Request $request)
    {
        $invoicesData = $this->accountingRepository->getInvoicesDataForDatatables($request, true);
        try {
            return Excel::download(new CFEExport($invoicesData), 'facturas-recibos-' . date('Y-m-d_H-i-s') . '.xlsx');
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Error al exportar los asientos a Excel. Por favor, intente nuevamente.');
        }
    }

    /**
     * Exporta los CFEs Invoices Receipts a PDF.
     *
     * @param Request $request
     * @return mixed
     */

    public function cfesInvoicesReceiptsExportPDF(Request $request)
    {
        $invoicesData = $this->accountingRepository->getInvoicesDataForDatatables($request, true);
        $pdf = Pdf::loadView('content.accounting.receipts.pdf', compact('invoicesData'));

        return $pdf->download('facturas-recibos-' . date('Y-m-d_H-i-s') . '.pdf');
    }

    /**
     * Exporta los CFEs Invoices Receipts a CSV.
     *
     * @param Request $request
     * @return mixed
     */

    public function cfesInvoicesReceiptsExportCSV(Request $request)
    {
        $invoicesData = $this->accountingRepository->getInvoicesDataForDatatables($request, true);

        try {
            return Excel::download(new CFEExport($invoicesData), 'facturas-recibos-' . date('Y-m-d_H-i-s') . '.csv', \Maatwebsite\Excel\Excel::CSV);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Error al exportar las facturas a CSV. Por favor, intente nuevamente.');
        }
    }

    /**
     * Obtiene los detalles de una factura.
     *
     * @param Request $request
     * @param string $invoice
     * @return JsonResponse
     */
    public function getInvoiceDetails(Request $request, $invoice)
    {
        try {
            $invoice = $this->accountingRepository->getInvoiceDetails($invoice);

            if (!$invoice) {
                return response()->json(['error' => 'No se encontró la factura especificada.'], 404);
            }

            return response()->json(['success' => true, 'data' => $invoice]);
        } catch (\Exception $e) {
            Log::error('Error al obtener los detalles de la factura: ' . $e->getMessage());
            return response()->json(['error' => 'Ocurrió un error al obtener los detalles de la factura.'], 500);
        }
    }

    /**
     * Obtiene los detalles de un recibo.
     *
     * @param Request $request
     * @param string $invoice
     * @return JsonResponse
     */

    public function exportPdf(Request $request)
    {
        $invoiceId = $request->invoice_id;
        $factura = $this->downloadCfePdf($invoiceId);
        $tempPdfPath = tempnam(sys_get_temp_dir(), 'pdf_');
        file_put_contents($tempPdfPath, $factura);
        return response()->download($tempPdfPath, 'factura.pdf');
    }

}
