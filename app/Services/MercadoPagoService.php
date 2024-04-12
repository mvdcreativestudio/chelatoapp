<?php

namespace App\Services;

use MercadoPago\SDK;
use MercadoPago\Preference;
use MercadoPago\Item;
use Illuminate\Support\Facades\Log;

class MercadoPagoService
{
    public function __construct()
    {
        // Configurar el acceso a la API de MercadoPago
        SDK::setAccessToken(config('services.mercadopago.access_token'));
    }

    public function createPreference($preferenceData, $order)
    {
        $preference = new Preference();

        // Configurar el pagador como un objeto
        $payer = new \stdClass();
        $payer->email = $preferenceData['payer']['email'];
        $preference->payer = $payer;
        $preference->currency_id = 'UYU';

        // Configurar los ítems
        $items = [];
        foreach ($preferenceData['items'] as $itemData) {
            $item = new Item();
            $item->title = $itemData['title'];
            $item->description = 'descripcion';
            $item->quantity = $itemData['quantity'];
            $item->unit_price = $itemData['unit_price'];
            $items[] = $item->toArray();
        }
        $preference->items = $items;


        $preference->back_urls = array(
          "success" => "https://google.com",
          "failure" => "https://mvdcreativestudio.com",
          "pending" => "https://sumeria.com.uy"
      );
          $preference->auto_return = "all";

        // // Configurar las URLs de retorno
        // $preference->notification_url = route('mercadopago.webhooks');

        // Guardar la preferencia
        $preference->save();
        Log::info('Preference created:', $preference->toArray());

        return $preference;
    }

    public function setCredentials($publicKey, $accessToken)
  {
    // Configurar el acceso a la API de MercadoPago con las credenciales de la tienda
    SDK::setPublicKey($publicKey);
    SDK::setAccessToken($accessToken);
}


}
