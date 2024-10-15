<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateCompanySettingsRequest;
use App\Repositories\CompanySettingsRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;

class CompanySettingsController extends Controller
{
  /**
   * El repositorio de configuración de la empresa.
   *
   * @var CompanySettingsRepository
  */
  protected CompanySettingsRepository $companySettingsRepository;

  /**
   * Constructor para inyectar el repositorio.
   *
   * @param CompanySettingsRepository $companySettingsRepository
  */
  public function __construct(CompanySettingsRepository $companySettingsRepository)
  {
    $this->companySettingsRepository = $companySettingsRepository;
  }

  /**
   * Muestra la página de configuración de la empresa.
   *
   * @return View
   */
  public function index(): View
  {
    $companySettings = $this->companySettingsRepository->getCompanySettings();
    return view('company-settings.index', compact('companySettings'));
  }

  /**
   * Actualiza la configuración de la empresa.
   *
   * @param UpdateCompanySettingsRequest $request
   * @return RedirectResponse
  */
  public function update(UpdateCompanySettingsRequest $request): RedirectResponse
  {
    Log::debug('Request reached controller', ['data' => $request->all()]);

    $validatedData = $request->validated();
    Log::debug('Validated data', ['data' => $validatedData]);

    $result = $this->companySettingsRepository->updateCompanySettings($validatedData);

    return redirect()->route('company-settings.index')->with($result['success'] ? 'success' : 'error', $result['message']);
  }
}
