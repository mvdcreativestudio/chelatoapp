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
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;

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
}
