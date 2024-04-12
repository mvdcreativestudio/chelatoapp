<?php

namespace App\Repositories;

use App\Models\OmniSetting;
use App\Models\Store;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\JsonResponse;
use App\Models\PhoneNumber;
use App\Http\Requests\UpdateMetaBusinessIdRequest;
use App\Http\Requests\UpdateMetaAdminTokenRequest;
use App\Http\Requests\AssociatePhoneNumberToStoreRequest;
use App\Http\Requests\DisassociatePhoneNumberFromStoreRequest;

class OmnichannelRepository
{
  /**
   * Base URL de la API de Facebook
   *
   * @var string
  */
  protected $baseUrl = 'https://graph.facebook.com/v17.0';

  /**
   * Token de acceso a la API de Facebook
   *
   * @var string
  */
  protected $token;

  /**
   * Inicializa el controlador
  */
  public function __construct()
  {
    $this->token = OmniSetting::where('setting_name', 'metaAdminToken')->value('setting_value');
  }

  /**
   * Actualiza o crea el ID del negocio (business_id) de Meta en la configuración.
   *
   * @param UpdateMetaBusinessIdRequest $request
   * @return \Illuminate\Database\Eloquent\Model
  */
  public function updateMetaBusinessId(UpdateMetaBusinessIdRequest $request): Model
  {
    $setting = OmniSetting::updateOrCreate(
      ['setting_name' => 'metaBusinessId'],
      ['setting_value' => $request->metaBusinessId]
    );

    return $setting;
  }

  /**
   * Actualiza o crea el token de administrador de Meta en la configuración.
   *
   * @param UpdateMetaAdminTokenRequest $request
   * @return \Illuminate\Database\Eloquent\Model
  */
  public function updateMetaAdminToken(UpdateMetaAdminTokenRequest $request): Model
  {
    $setting = OmniSetting::updateOrCreate(
      ['setting_name' => 'metaAdminToken'],
      ['setting_value' => $request->metaAdminToken]
    );

    return $setting;
  }

  /**
   * Asocia un número de teléfono a una Store.
   *
   * @param AssociatePhoneNumberToStoreRequest $request
   * @return void
  */
  public function associatePhoneNumberToStore(AssociatePhoneNumberToStoreRequest $request): void
  {
    PhoneNumber::updateOrCreate([
      'phone_id' => $request->phone_id,
    ], [
      'phone_number' => $request->phone_number,
      'store_id' => $request->store_id,
      'is_store' => true,
    ]);
  }

  /**
   * Desasocia un número de teléfono de una Store.
   *
   * @param DisassociatePhoneNumberFromStoreRequest $request
   * @return void
  */
  public function disassociatePhoneNumberFromStore(DisassociatePhoneNumberFromStoreRequest $request): void
  {
    $phoneNumber = PhoneNumber::where('phone_id', $request->phone_id)->first();

    if ($phoneNumber) {
      $phoneNumber->update([
        'store_id' => null,
        'is_store' => false,
      ]);
    }
  }

  /**
   * Obtiene los números asociados a una cuenta de WhatsApp Business.
   *
   * @param string $businessId
   * @return \Illuminate\Http\JsonResponse|array
   */
  public function getPhoneNumbers($whatsAppBusinessAccountId): JsonResponse|array {
    $response = Http::withToken($this->token)->get("{$this->baseUrl}/{$whatsAppBusinessAccountId}/phone_numbers");

    if ($response->successful()) {
        $data = $response->json();
        return $data['data'];
    } else {
        return response()->json(['error' => $response->body()], $response->status());
    }
  }

  /**
   * Obtiene las cuentas de WhatsApp Business asociadas al negocio de Meta.
   *
   * @return \Illuminate\Http\JsonResponse|array
  */
  public function getOwnedWABusinessAccounts(): JsonResponse|array
  {
    $businessId = OmniSetting::where('setting_name', 'metaBusinessId')->value('setting_value');

    if (!$businessId) {
      return response()->json(['error' => 'No se ha configurado el ID de negocio de Meta.'], 400);
    }

    $url = "{$this->baseUrl}/{$businessId}/owned_whatsapp_business_accounts";
    $response = Http::withToken($this->token)
                  ->withHeaders(['Content-Type' => 'application/json'])
                  ->get($url);

    if ($response->successful()) {
      $numberList = $response->json()['data'] ?? [];
      return $numberList;
    } else {
      return response()->json(['error' => 'No se pudo obtener las cuentas de negocio.'], 500);
    }
  }

  /**
    * Obtiene las cuentas de WhatsApp Business y sus números de teléfono asociados.
    *
    * @return \Illuminate\Http\JsonResponse|array
  */
  public function getWhatsAppBusinessData(): JsonResponse|array
  {
      $businessId = OmniSetting::where('setting_name', 'metaBusinessId')->value('setting_value');

      if (!$businessId) {
          return [];
      }

      $accounts = $this->getOwnedWABusinessAccounts();

      if (!is_array($accounts)) {
          return [];
      }

      foreach ($accounts as $key => $account) {
          $phoneNumbers = $this->getPhoneNumbers($account['id']);

          if (!is_array($phoneNumbers)) {
              $phoneNumbers = [];
          }

          $accounts[$key]['phone_numbers'] = $phoneNumbers;
      }

      return $accounts;
  }

  /**
   * Devuelve datos necesarios para la vista de configuración de omnicanalidad.
   *
   * @return array
   */
  public function settings(): array {
    $whatsAppBusinessData = $this->getWhatsAppBusinessData();
    $metaBusinessId = OmniSetting::where('setting_name', 'metaBusinessId')->value('setting_value');
    $metaAdminToken = OmniSetting::where('setting_name', 'metaAdminToken')->value('setting_value');
    $associatedPhoneIds = PhoneNumber::where('is_store', true)->pluck('phone_id')->toArray();
    $storesNotAssociated = Store::whereDoesntHave('phoneNumber')->get();

    foreach ($whatsAppBusinessData as $key => $account) {
      foreach ($account['phone_numbers'] as $phoneKey => $phone) {
        if (in_array($phone['id'], $associatedPhoneIds)) {
            $phoneNumber = PhoneNumber::where('phone_id', $phone['id'])->first();
            $store = $phoneNumber->store;
            $whatsAppBusinessData[$key]['phone_numbers'][$phoneKey]['store'] = $store;
        } else {
            $whatsAppBusinessData[$key]['phone_numbers'][$phoneKey]['store'] = null;
        }
      }
    }

    return compact('whatsAppBusinessData', 'metaBusinessId', 'metaAdminToken', 'storesNotAssociated');
  }

}
