<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Repositories\OmnichannelRepository;
use App\Http\Requests\UpdateMetaBusinessIdRequest;
use App\Http\Requests\UpdateMetaAdminTokenRequest;
use App\Http\Requests\AssociatePhoneNumberToStoreRequest;
use App\Http\Requests\DisassociatePhoneNumberFromStoreRequest;
use App\Models\Message;
use App\Models\PhoneNumber;

class OmnichannelController extends Controller
{

    /**
     * El repositorio para las operaciones de omnicanalidad.
     *
     * @var OmnichannelRepository
    */
    protected OmnichannelRepository $omnichannelRepo;

    /**
     * Inicializa el controlador
    */
    public function __construct(OmnichannelRepository $omnichannelRepo)
    {
        $this->omnichannelRepo = $omnichannelRepo;
    }

    /**
     * Muestra la vista de chats
     *
     * @return \Illuminate\View\View
    */
    public function chats() {
        $user = auth()->user();
        $userStore = $user->store;

        if (!$userStore) {
            return view('omnichannel.chats')->with('error', 'No tienes una tienda asociada.');
        }

        $phoneNumber = $userStore->phoneNumber;

        if (!$phoneNumber) {
            return view('omnichannel.chats')->with('error', 'Tu tienda no tiene un número de teléfono asociado.');
        }

        $chats = $phoneNumber->getLastMessagesForChats();

        return view('omnichannel.chats', compact('chats'));
    }


    /**
     * Muestra la vista de configuración de la omnicanalidad y adjunta la WhatsApp Business Data.
     *
     * @return \Illuminate\View\View
    */
    public function settings(): View {
      $settings = $this->omnichannelRepo->settings();

      extract($settings);

      return view('omnichannel.settings', compact('whatsAppBusinessData', 'metaBusinessId', 'metaAdminToken', 'storesNotAssociated'));
    }

    /**
     * Actualiza o crea el ID del negocio (business_id) de Meta en la configuración.
     *
     * @param UpdateMetaBusinessIdRequest $request
     * @return \Illuminate\Http\RedirectResponse
    */
    public function updateMetaBusinessId(UpdateMetaBusinessIdRequest $request): RedirectResponse {
      $this->omnichannelRepo->updateMetaBusinessId($request);
      return back()->with('success', 'ID de negocio actualizado correctamente.');
    }

    /**
     * Actualiza o crea el token de administrador de Meta en la configuración.
     *
     * @param UpdateMetaAdminTokenRequest $request
     * @return \Illuminate\Http\RedirectResponse
    */
    public function updateMetaAdminToken(UpdateMetaAdminTokenRequest $request): RedirectResponse {
      $this->omnichannelRepo->updateMetaAdminToken($request);
      return back()->with('success', 'Token de administrador actualizado correctamente.');
    }

    /**
     * Asocia un número de teléfono a una Store.
     *
     * @param AssociatePhoneNumberToStoreRequest $request
     * @return \Illuminate\Http\RedirectResponse
    */
    public function associatePhoneNumberToStore(AssociatePhoneNumberToStoreRequest $request): RedirectResponse {
      $this->omnichannelRepo->associatePhoneNumberToStore($request);
      return back()->with('success', 'Número de teléfono asociado correctamente.');
    }

    /**
     * Desasocia un número de teléfono de una Store.
     *
     * @param DisassociatePhoneNumberFromStoreRequest $request
     * @return \Illuminate\Http\RedirectResponse
    */
    public function disassociatePhoneNumberFromStore(DisassociatePhoneNumberFromStoreRequest $request): RedirectResponse {
      $this->omnichannelRepo->disassociatePhoneNumberFromStore($request);
      return back()->with('success', 'Número de teléfono desasociado correctamente.');
    }
}
