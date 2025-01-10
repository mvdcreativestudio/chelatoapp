<?php

namespace App\Repositories;

use App\Enums\MercadoPago\MercadoPagoApplicationTypeEnum;
use App\Models\CashRegister;
use App\Models\MercadoPagoAccountPOS;
use App\Models\MercadoPagoAccountStore;
use App\Services\MercadoPagoService;
use Exception;
use Illuminate\Support\Facades\DB;

class MercadoPagoAccountPOSRepository
{

    /**
     * El servicio de MercadoPago.
     * 
     * @var MercadoPagoService
     */
    protected $mercadoPagoService;


    /**
     * Inyecta los servicios necesarios.
     *
     * @param MercadoPagoService $mercadoPagoService
     */

    public function __construct(MercadoPagoService $mercadoPagoService)
    {
        $this->mercadoPagoService = $mercadoPagoService;
    }


    /**
     * Obtiene todos los POS de MercadoPago.
     *
     * @return mixed
     */
    public function getAllPOS(): mixed
    {
        return MercadoPagoAccountPOS::all();
    }

    /**
     * Obtiene un POS específico por su ID.
     *
     * @param int $posId
     * @return MercadoPagoAccountPOS
     */
    public function getPOSById(int $posId): MercadoPagoAccountPOS
    {
        return MercadoPagoAccountPOS::findOrFail($posId);
    }

    /**
     * Crea un nuevo POS en la base de datos.
     *
     * @param array $data
     * @return MercadoPagoAccountPOS
     */
    public function store(array $data): MercadoPagoAccountPOS
    {
        DB::beginTransaction();

        $cashRegister = CashRegister::findOrFail($data['cash_register_id']);
        $mercadoPagoAccountStore = MercadoPagoAccountStore::with('mercadopagoAccount')->whereHas('mercadopagoAccount', function ($query) use ($data) {
            $query->where('store_id', $data['store_id']);
        })->first();
        try {
            $this->mercadoPagoService->setCredentials($data['store_id'], MercadoPagoApplicationTypeEnum::PAID_PRESENCIAL->value);
            $resultPosMercadoPago = $this->mercadoPagoService->createPOS([
                'name' => $data['name'],
                'fixed_amount' => true,
                'category' => null,
                'external_id' => 'STORE' . $mercadoPagoAccountStore->id . 'POS' . $cashRegister->id,
                'external_store_id' => $mercadoPagoAccountStore->external_id,
                'store_id' => $mercadoPagoAccountStore->store_id
            ]);
            $pos = MercadoPagoAccountPOS::create([
                'id_pos' => $resultPosMercadoPago['id'],
                'name' => $resultPosMercadoPago['name'],
                'fixed_amount' => true,
                'category' => null,
                'qr_image' => $resultPosMercadoPago['qr']['image'],
                'template_document' => $resultPosMercadoPago['qr']['template_document'],
                'template_image' => $resultPosMercadoPago['qr']['template_image'],
                'qr_code' => $resultPosMercadoPago['qr_code'],
                'store_id' => $resultPosMercadoPago['store_id'],
                'external_id' => $resultPosMercadoPago['external_id'],
                'external_store_id' => $mercadoPagoAccountStore->external_id,
                'mercado_pago_account_store_id' => $mercadoPagoAccountStore->id,
                'cash_register_id' => $data['cash_register_id'],
            ]);
            DB::commit();
            return $pos;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    /**
     * Actualiza un POS existente.
     *
     * @param MercadoPagoAccountPOS $pos
     * @param array $data
     * @return MercadoPagoAccountPOS
     */
    public function update(MercadoPagoAccountPOS $pos, array $data): MercadoPagoAccountPOS
    {
        DB::beginTransaction();

        try {
            $this->mercadoPagoService->setCredentials($data['store_id'], MercadoPagoApplicationTypeEnum::PAID_PRESENCIAL->value);
            $this->mercadoPagoService->updatePOS($pos->id_pos, [
                'name' => $data['name'],
                'fixed_amount' => true,
                'category' => null,
            ]);
            $pos->update([
                'name' => $data['name'],
                'fixed_amount' => true,
                'category' => null,
            ]);
            DB::commit();
            return $pos;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Elimina un POS específico.
     *
     * @param int $posId
     * @return void
     */
    public function destroy(int $posId): void
    {
        try {
            $pos = MercadoPagoAccountPOS::where('cash_register_id', $posId)->first();
            $this->mercadoPagoService->setCredentials($pos->cashRegister->store_id, MercadoPagoApplicationTypeEnum::PAID_PRESENCIAL->value);
            $this->mercadoPagoService->deletePOS($pos->id_pos);
            $pos->delete();
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Obtiene un POS por external_id.
     *
     * @param string $externalId
     * @return MercadoPagoAccountPOS|null
     */
    public function getPOSByExternalId(string $externalId): ?MercadoPagoAccountPOS
    {
        return MercadoPagoAccountPOS::where('external_id', $externalId)->first();
    }

    /**
     * Obtiene los POS asociados a una sucursal de MercadoPago.
     *
     * @param int $mercadoPagoAccountStoreId
     * @return mixed
     */
    public function getPOSByStoreId(int $mercadoPagoAccountStoreId): mixed
    {
        return MercadoPagoAccountPOS::where('mercado_pago_account_store_id', $mercadoPagoAccountStoreId)->get();
    }

    /**
     * Obtiene los POS asociados a una caja registradora.
     *
     * @param int $cashRegisterId
     * @return mixed
     */
    public function getPOSByCashRegisterId(int $cashRegisterId): mixed
    {
        return MercadoPagoAccountPOS::where('cash_register_id', $cashRegisterId)->first();
    }
}
