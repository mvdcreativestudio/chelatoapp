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

          // Log the existing logo path and new file
          Log::debug('Current logo path in DB', ['logo_black' => $companySettings->logo_black]);
          Log::debug('Data received', $data);

          // Verifica si se subió un nuevo archivo para `logo_black`
          if (isset($data['logo_black']) && $data['logo_black'] instanceof \Illuminate\Http\UploadedFile) {
              Log::debug('New file uploaded', ['original_name' => $data['logo_black']->getClientOriginalName()]);

              // Generar un nombre único para el archivo con su extensión
              $fileName = Str::uuid() . '.' . $data['logo_black']->getClientOriginalExtension();
              Log::debug('Generated unique filename', ['fileName' => $fileName]);

              // Define la ruta de destino en `public/assets/img/branding`
              $destinationPath = public_path('assets/img/branding');
              Log::debug('Destination path', ['path' => $destinationPath]);

              // Verificar si el directorio existe y es escribible
              if (!is_dir($destinationPath) || !is_writable($destinationPath)) {
                  Log::error('Destination path is not writable or does not exist', ['path' => $destinationPath]);
                  return ['success' => false, 'message' => 'No se pudo escribir en la ruta destino.'];
              }

              // Mover el archivo a la ubicación especificada
              $data['logo_black']->move($destinationPath, $fileName);
              Log::debug('File moved successfully', ['path' => $destinationPath . '/' . $fileName]);

              // Guardar la ruta relativa en la base de datos
              $data['logo_black'] = 'assets/img/branding/' . $fileName;
              Log::debug('Relative path saved in data', ['logo_black' => $data['logo_black']]);
          } else {
              Log::debug('No new file uploaded or not a valid file');
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
