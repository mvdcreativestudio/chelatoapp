<?php

namespace App\Http\Controllers;

use MercadoPago\SDK;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;


class MercadoPagoController extends Controller
{
  public function __construct()
  {
    SDK::setAccessToken(config('services.mercadopago.access_token'));
  }

  public function webhooks(Request $request)
{
    // Obtener la secret key desde las variables de entorno
    $secretKey = '3710b5958d74218400f731bff6165a242c30851b8935c97912dcb753ed3587a9';

    // Registrar los datos recibidos antes de calcular la firma HMAC
    Log::info('Datos recibidos de MercadoPago:', [
        'headers' => $request->header(),
        'body' => $request->all(),
    ]);

    // Asegurarse de que el cuerpo de la solicitud tiene el formato esperado
    $payload = $request->all();
    if (isset($payload['data']) && isset($payload['data']['id'])) {
        // Extraer la firma del encabezado
        $xSignatureParts = explode(',', $request->header('x-signature'));
        $xSignatureData = [];
        foreach ($xSignatureParts as $part) {
            list($key, $value) = explode('=', $part);
            $xSignatureData[trim($key)] = trim($value);
        }

        $dataID = $payload['data']['id'];
        $ts = isset($xSignatureData['ts']) ? $xSignatureData['ts'] : '';
        $receivedHash = isset($xSignatureData['v1']) ? $xSignatureData['v1'] : '';

        // Construir el mensaje a firmar
        $message = "id:$dataID;request-id:{$request->header('x-request-id')};ts:$ts;";

        // Crear una firma HMAC utilizando la función hash_hmac
        $generatedHash = hash_hmac('sha256', $message, $secretKey);

        // Comparar la firma generada con la firma recibida
        if (hash_equals($generatedHash, $receivedHash)) {
            // La firma coincide, lo que significa que la notificación es auténtica
            // Realiza las acciones necesarias en respuesta a la notificación recibida
            // Devuelve un HTTP STATUS 200 (OK) o 201 (CREATED) para confirmar la recepción
            Log::info('La verificación HMAC pasó correctamente');
            return response()->json(['message' => 'HMAC verification passed'], 200);
        } else {
            // La firma no coincide, lo que indica un posible intento de falsificación
            // Devuelve un HTTP STATUS 400 (Bad Request) u otro código de error apropiado
            Log::error('La verificación HMAC falló');
            return response()->json(['error' => 'HMAC verification failed'], 400);
        }
    } else {
        // Si el índice 'data' o 'data.id' no está presente, registra un error y devuelve una respuesta de error
        Log::error('El índice "data" o "data.id" no está presente en los datos de la solicitud');
        return response()->json(['error' => 'Invalid request data'], 400);
    }
}


}
