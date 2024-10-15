<?php

namespace App\Services;

use Maatwebsite\Excel\Facades\Excel;
use App\Exports\GenericExport;
use Illuminate\Http\Request;

class ExportService
{
    public function exportToExcel(Request $request, $model, $filename)
    {
        $filters = $request->all();  // Captura los filtros de la solicitud
        $data = $model->filterData($filters)->get();  // Aplica los filtros al modelo

        return Excel::download(new GenericExport($data), $filename . '.xlsx');
    }
}
