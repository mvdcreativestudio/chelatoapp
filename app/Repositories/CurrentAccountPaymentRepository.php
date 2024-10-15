<?php

namespace App\Repositories;

use App\Enums\CurrentAccounts\StatusPaymentEnum;
use App\Models\Client;
use App\Models\CurrentAccount;
use App\Models\CurrentAccountPayment;
use App\Models\CurrentAccountSettings;
use App\Models\PaymentMethod;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class CurrentAccountPaymentRepository
{
    /**
     * Obtiene todos los pagos de cuentas corrientes filtrados por tipo de transacción.
     *
     * @param int $id
     * @return array
     */
    public function getAllCurrentAccountPayments(int $id): array
    {

        $currentAccount = CurrentAccount::with(['client', 'supplier', 'initialCredits', 'payments'])
            ->where('id', $id)
            ->first();

        if (!$currentAccount) {
            throw new \Exception('No se encontró la cuenta corriente.');
        }

        $totalAmount = $currentAccount->payments->sum('payment_amount');

        $totalDebit = $currentAccount->initialCredits->sum('total_debit');

        $countInitialCredits = $currentAccount->initialCredits->count();
        $countPayments = $currentAccount->payments->count();

        $typeEntity = $currentAccount->client_id ? 'client' : 'supplier';
        $dataEntity = null;
        if ($typeEntity === 'client') {
            $dataEntity = Client::find($currentAccount->client_id);
        } else {
            $dataEntity = Supplier::find($currentAccount->supplier_id);
        }

        $currentAccountStatus = $this->getCurrentAccountStatus();

        return compact('currentAccount', 'totalAmount', 'totalDebit', 'countInitialCredits', 'countPayments', 'typeEntity', 'dataEntity', 'currentAccountStatus');
    }

    /**
     * Obtiene una cuenta corriente por su ID.
     *
     * @param int $currentAccountId
     * @return CurrentAccount
     */
    public function getCurrentAccount($currentAccountId)
    {
        return CurrentAccount::find($currentAccountId);
    }

    /**
     * Almacena un nuevo pago de cuenta corriente en la base de datos.
     *
     * @param array $data
     * @return CurrentAccountPayment
     */
    public function storePayment(array $data): CurrentAccountPayment
    {
        DB::beginTransaction();

        try {
            // Crear el pago de la cuenta corriente
            $currentAccountPayment = CurrentAccountPayment::create([
                'current_account_id' => $data['current_account_id'],
                'payment_amount' => $data['payment_amount'],
                'payment_date' => $data['payment_date'],
                'payment_method_id' => $data['payment_method_id'],
            ]);

            // Actualizar el estado de la cuenta corriente (pagada o parcial)
            $currentAccount = CurrentAccount::find($data['current_account_id']);
            $this->updateCurrentAccountStatus($currentAccount);

            DB::commit();

            return $currentAccountPayment;

        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Error al crear el pago de la cuenta corriente: ' . $e->getMessage());
        }
    }

    /**
     * Actualiza un pago de cuenta corriente específico.
     *
     * @param int $paymentId
     * @param array $data
     * @return CurrentAccountPayment
     */
    public function updatePayment(int $paymentId, array $data): CurrentAccountPayment
    {
        DB::beginTransaction();

        try {
            $payment = CurrentAccountPayment::findOrFail($paymentId);
            $payment->update([
                'current_account_id' => $data['current_account_id'],
                'payment_amount' => $data['payment_amount'],
                'payment_date' => $data['payment_date'],
                'payment_method_id' => $data['payment_method_id'],
            ]);

            // Actualizar el estado de la cuenta corriente
            $this->updateCurrentAccountStatus($payment->currentAccount);

            DB::commit();

            return $payment;

        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Error al actualizar el pago de la cuenta corriente: ' . $e->getMessage());
        }
    }

    /**
     * Elimina un pago de cuenta corriente específico.
     *
     * @param int $paymentId
     * @return bool
     */
    public function destroyPayment(int $paymentId): bool
    {
        DB::beginTransaction();
        try {
            $payment = CurrentAccountPayment::with('currentAccount')->findOrFail($paymentId);
            $currentAccount = $payment->currentAccount;

            $payment->delete();

            // Actualizar el estado de la cuenta corriente
            $this->updateCurrentAccountStatus($currentAccount);

            DB::commit();

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Error al eliminar el pago de la cuenta corriente: ' . $e->getMessage());
        }
    }

    /**
     * Elimina múltiples pagos de cuentas corrientes.
     *
     * @param array $ids
     * @return void
     */

    public function deleteMultiple(array $ids): void
    {
        DB::beginTransaction();

        try {
            $payments = CurrentAccountPayment::whereIn('id', $ids)->get();
            $currentAccount = $payments->first()->currentAccount;

            foreach ($payments as $payment) {
                $payment->delete();
            }

            // Actualizar el estado de la cuenta corriente
            $this->updateCurrentAccountStatus($currentAccount);

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Error al eliminar los pagos de la cuenta corriente: ' . $e->getMessage());
        }
    }

    /**
     * Obtiene los pagos de una cuenta corriente para la DataTable.
     *
     * @param int $accountId
     * @return mixed
     */
    public function getPaymentsForDataTable(int $accountId): mixed
    {
        $query = CurrentAccountPayment::where('current_account_id', $accountId)
            ->select(['payment_amount', 'payment_date', 'payment_method_id', 'currency_id']);

        return datatables()->eloquent($query)->make(true);
    }

    /**
     * Actualiza el estado de la cuenta corriente en función de los pagos.
     *
     * @param CurrentAccount $currentAccount
     * @return void
     */
    private function updateCurrentAccountStatus(CurrentAccount $currentAccount): void
    {
        $totalPaid = $currentAccount->payments->sum('payment_amount');

        if ($totalPaid >= $currentAccount->payment_total_debit) {
            $currentAccount->status = 'Paid';
        } elseif ($totalPaid > 0) {
            $currentAccount->status = 'Partial';
        } else {
            $currentAccount->status = 'Unpaid';
        }

        $currentAccount->save();
    }

    /**
     * Obtiene un pago de cuenta corriente por su ID.
     *
     * @param int $paymentId
     * @return CurrentAccountPayment
     */
    public function getPaymentById(int $paymentId): CurrentAccountPayment
    {
        return CurrentAccountPayment::findOrFail($paymentId);
    }

    /**
     * Obtiene todos los métodos de pago.
     *
     * @return Collection
     */
    public function getPaymentMethods()
    {
        return PaymentMethod::all();
    }

    /**
     * Obtiene las configuraciones de cuentas corrientes.
     *
     * @return Collection
     */
    public function getCurrentAccountSettings()
    {
        return CurrentAccountSettings::all();
    }

    /**
     * Obtiene el cliente por la cuenta corriente actual.
     *
     * @return Collection
     */
    public function getClientByCurrentAccount($currentAccountId)
    {
        return Client::whereHas('currentAccount', function ($query) use ($currentAccountId) {
            $query->where('id', $currentAccountId);
        })->first();
    }

    /**
     * Obtiene el proveedor por la cuenta corriente actual.
     *
     * @return Collection|null
     */
    public function getSupplierByCurrentAccount($currentAccountId)
    {
        return Supplier::whereHas('currentAccount', function ($query) use ($currentAccountId) {
            $query->where('id', $currentAccountId);
        })->first();
    }

    public function getCurrentAccountStatus()
    {
        return StatusPaymentEnum::getTranslateds();
    }
}
