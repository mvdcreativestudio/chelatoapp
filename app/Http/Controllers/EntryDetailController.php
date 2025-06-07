<?php

namespace App\Http\Controllers;

use App\Exports\EntryDetailsExport;
use App\Models\EntryDetail;
use App\Repositories\EntryDetailRepository;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class EntryDetailController extends Controller
{
    /**
     * El repositorio para las operaciones de detalles de asientos.
     *
     * @var EntryDetailRepository
     */
    protected $entryDetailRepository;

    /**
     * Inyecta el repositorio en el controlador y los middleware.
     *
     * @param EntryDetailRepository $entryDetailRepository
     */
    public function __construct(EntryDetailRepository $entryDetailRepository)
    {
        $this->middleware(['check_permission:access_entries', 'user_has_store'])->only(
            [
                'show',
                'details',
                'datatable',
            ]
        );

        $this->entryDetailRepository = $entryDetailRepository;
    }

    /**
     * Muestra los detalles de un asiento específico.
     *
     * @param int $entryId
     * @return View
     */
    public function show(int $entryId): View
    {
        try {
            // Obtener los detalles del asiento utilizando el repositorio
            $entry = $this->entryDetailRepository->getEntryDetailsByEntryId($entryId);
            $entryDetails = $entry->details;
            return view('content.accounting.entries.entry-details.index', compact('entry', 'entryDetails'));
        } catch (\Exception $e) {
            Log::error("Error al obtener los detalles del asiento con ID {$entryId}: " . $e->getMessage());
            abort(404, 'Los detalles del asiento no fueron encontrados.');
        }
    }

    /**
     * Elimina un detalle de asiento específico.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            // Encuentra el detalle del asiento
            $entryDetail = EntryDetail::findOrFail($id);

            // Obtiene el asiento asociado
            $entry = $entryDetail->entry;

            // Elimina el detalle del asiento
            $entryDetail->delete();

            // Recalcula el balance del asiento
            $entry->is_balanced = $entry->calculateBalance();
            $entry->save();

            DB::commit();

            return response()->json(['success' => 'El detalle del asiento ha sido eliminado y el balance actualizado.']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al eliminar el detalle del asiento con ID {$id}: " . $e->getMessage());
            return response()->json(['error' => 'Error al eliminar el detalle del asiento.'], 400);
        }
    }

    /**
     * Devuelve los detalles de un asiento específico en formato JSON.
     *
     * @param int $entryId
     * @return JsonResponse
     */
    public function detail(int $entryId): JsonResponse
    {
        try {
            $entryDetails = $this->entryDetailRepository->getEntryDetailsForDataTable($entryId);

            return response()->json($entryDetails);
        } catch (\Exception $e) {
            Log::error("Error al obtener los detalles del asiento con ID {$entryId}: " . $e->getMessage());
            return response()->json(['error' => 'Error al obtener los detalles del asiento.'], 400);
        }
    }

    /**
     * Obtiene los detalles del asiento para la DataTable.
     *
     * @param int $entryId
     * @return mixed
     */
    public function datatable(int $entryId): mixed
    {
        try {
            return $this->entryDetailRepository->getEntryDetailsForDataTable($entryId);
        } catch (\Exception $e) {
            Log::error("Error al obtener los detalles del asiento para DataTable con ID {$entryId}: " . $e->getMessage());
            return response()->json(['error' => 'Error al cargar los detalles del asiento para la tabla.'], 400);
        }
    }

    public function exportExcel($entryId)
    {
        try {
            // Obtener el asiento por su ID con todos sus detalles
            $entry = $this->entryDetailRepository->getEntryWithDetails($entryId);

            // Generar y descargar el archivo Excel
            return Excel::download(new EntryDetailsExport($entry), 'detalle-asiento-' . $entryId . '-' . date('Y-m-d_H-i-s') . '.xlsx');
        } catch (\Exception $e) {
            // Log de errores y redirección en caso de error
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Error al exportar el asiento a Excel. Por favor, intente nuevamente.');
        }
    }

    public function exportPdf($entryId)
    {
        try {
            // Obtener el asiento por su ID con todos sus detalles
            $entry = $this->entryDetailRepository->getEntryWithDetails($entryId);
            // dd($entry);
            // Generar el PDF utilizando la vista correspondiente
            $pdf = Pdf::loadView('content.accounting.entries.entry-details.export-pdf', compact('entry'));

            // Descargar el archivo PDF
            return $pdf->download('detalle-asiento-' . $entryId . '-' . date('Y-m-d_H-i-s') . '.pdf');
        } catch (\Exception $e) {
            dd($e->getMessage());
            // Log de errores y redirección en caso de error
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Error al exportar el asiento a PDF. Por favor, intente nuevamente.');
        }
    }
}
