<?php

namespace App\Services\POS;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class ScanntechAuthService
{
    private $authUrl = 'https://sso-dev.scanntech.com/auth/realms/scannsae/protocol/openid-connect/token'; // URL de autenticación de Scanntech
    private $clientId = 'scannsae-client'; // ID de cliente de prueba Scanntech
    private $username = 'mvdstudio'; // Usuario de prueba Scanntech
    private $password = 'Mvdstudio.2024'; // Contraseña de prueba Scanntech

    /**
     * Obtener el token de acceso desde Scanntech.
     *
     * @return string|null
     */
    public function getAccessToken()
    {
        // Verificar si el token ya está en caché
        $cachedToken = Cache::get('scanntech_token');

        if ($cachedToken) {
            return $cachedToken;
        }

        // Hacer la solicitud para obtener el token
        $response = Http::asForm()->post($this->authUrl, [
            'client_id' => 'scannsae-client',
            'username' => 'mvdstudio',
            'password' => 'Mvdstudio.2024',
            'grant_type' => 'password',
        ]);

        // Verificar si la solicitud fue exitosa
        if ($response->successful()) {
            $token = $response->json('access_token');

            // Almacenar el token en caché por su tiempo de expiración (ej. 1 hora)
            Cache::put('scanntech_token', $token, now()->addMinutes(3));

            return $token;
        }

        // Manejar errores de autenticación
        \Log::error('Error al obtener el token de Scanntech: ' . $response->body());

        return null;
    }
}
