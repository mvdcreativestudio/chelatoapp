<?php

namespace App\Repositories;

use App\Models\CompanySettings;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

          // Log the existing settings and new data
          Log::debug('Current settings in DB', ['logo_black' => $companySettings->logo_black, 'hero_image' => $companySettings->hero_image]);
          Log::debug('Data received', $data);

          // Procesar logo_black
          if (isset($data['logo_black']) && $data['logo_black'] instanceof \Illuminate\Http\UploadedFile) {
              Log::debug('New logo_black uploaded', ['original_name' => $data['logo_black']->getClientOriginalName()]);

              $fileName = Str::uuid() . '.' . $data['logo_black']->getClientOriginalExtension();
              $destinationPath = public_path('assets/img/branding');

              if (!is_dir($destinationPath) || !is_writable($destinationPath)) {
                  Log::error('Destination path for logo_black is not writable or does not exist', ['path' => $destinationPath]);
                  return ['success' => false, 'message' => 'No se pudo escribir en la ruta destino para logo_black.'];
              }

              $data['logo_black']->move($destinationPath, $fileName);
              $data['logo_black'] = 'assets/img/branding/' . $fileName;
              Log::debug('New logo_black saved', ['logo_black' => $data['logo_black']]);
          } else {
              Log::debug('No new logo_black uploaded or not a valid file');
          }

          // Procesar hero_image
          if (isset($data['hero_image']) && $data['hero_image'] instanceof \Illuminate\Http\UploadedFile) {
              Log::debug('New hero_image uploaded', ['original_name' => $data['hero_image']->getClientOriginalName()]);

              $fileName = Str::uuid() . '.' . $data['hero_image']->getClientOriginalExtension();
              $destinationPath = public_path('assets/img/branding');

              if (!is_dir($destinationPath) || !is_writable($destinationPath)) {
                  Log::error('Destination path for hero_image is not writable or does not exist', ['path' => $destinationPath]);
                  return ['success' => false, 'message' => 'No se pudo escribir en la ruta destino para hero_image.'];
              }

              $data['hero_image']->move($destinationPath, $fileName);
              $data['hero_image'] = 'assets/img/branding/' . $fileName;
              Log::debug('New hero_image saved', ['hero_image' => $data['hero_image']]);
          } else {
              Log::debug('No new hero_image uploaded or not a valid file');
          }

          // Actualizar la configuración en la base de datos
          $companySettings->update($data);
          Log::debug('Company settings updated in DB', ['data' => $data]);

          return ['success' => true, 'message' => 'Configuración actualizada correctamente.'];
      } catch (\Exception $e) {
          Log::error('Error al actualizar la configuración de la empresa: ' . $e->getMessage());
          return ['success' => false, 'message' => 'No se pudo actualizar la configuración.'];
      }
  }

}
