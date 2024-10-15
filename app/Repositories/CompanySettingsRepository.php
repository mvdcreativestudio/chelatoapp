<?php

namespace App\Repositories;

use App\Models\CompanySettings;
use Illuminate\Support\Facades\Log;

class CompanySettingsRepository
{
  /**
   * Obtiene la configuración de la empresa.
   *
   * @return CompanySettings
  */
  public function getCompanySettings(): CompanySettings
  {
    return CompanySettings::firstOrFail();
  }

  /**
   * Actualiza la configuración de la empresa.
   *
   * @param array $data
   * @return array
  */
  public function updateCompanySettings(array $data): array
  {
    try {
        $companySettings = CompanySettings::firstOrFail();
        $companySettings->update($data);

        return ['success' => true, 'message' => 'Configuración actualizada correctamente.'];
    } catch (\Exception $e) {
        Log::error('Error al actualizar la configuración de la empresa: ' . $e->getMessage());
        return ['success' => false, 'message' => 'No se pudo actualizar la configuración.'];
    }
  }
}
