<?php

namespace App\Repositories;

use App\Enums\CurrentAccounts\StatusPaymentEnum;
use App\Enums\CurrentAccounts\TransactionTypeEnum;
use App\Helpers\Helpers;
use App\Models\Client;
use App\Models\Currency;
use App\Models\CurrentAccount;
use App\Models\CurrentAccountInitialCredit;
use App\Models\CurrentAccountPayment;
use App\Models\CurrentAccountSettings;
use App\Models\PaymentMethod;
use App\Models\Supplier;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class CurrentAccountRepository
{
    /**
     * Obtiene todas las cuentas corrientes filtradas por tipo de transacción.
     *
     * @param string $transactionType
     * @return Collection
     */
    public function getAllCurrentAccounts(): array
    {

        $currentAccounts = CurrentAccount::with(['client', 'supplier', 'initialCredits', 'payments'])->get();
        $totalAmount = $currentAccounts->map(function ($account) {
            return $account->payments->sum('payment_amount');
        })->sum();
        $totalDebit = $currentAccounts->map(function ($account) {
            return $account->initialCredits->sum('total_debit');
        })->sum();

        $paidAccounts = CurrentAccount::where('status', StatusPaymentEnum::PAID)->count();

        $partialAccounts = CurrentAccount::where('status', StatusPaymentEnum::PARTIAL)->count();

        $unpaidAccounts = CurrentAccount::where('status', StatusPaymentEnum::UNPAID)->count();
        return compact('currentAccounts', 'totalAmount', 'totalDebit', 'paidAccounts', 'partialAccounts', 'unpaidAccounts');
    }

    /**
     * Obtiene una cuenta corriente por su ID.
     *
     * @param int $id
     * @return CurrentAccount
     */
    public function getCurrentAccountById(int $id): CurrentAccount
    {
        return CurrentAccount::with(['client', 'supplier', 'currency', 'initialCredits', 'payments'])->findOrFail($id);
    }

    /**
     * Almacena una nueva cuenta corriente en la base de datos.
     *
     * @param array $data
     * @return CurrentAccount
     */
    public function store(array $data): CurrentAccount
    {
        // Comenzamos la transacción para asegurar atomicidad
        DB::beginTransaction();
        try {
            // Encontrar la configuración de la cuenta corriente
            $currentAccountSettings = CurrentAccountSettings::findOrFail($data['current_account_settings_id']);

            // Calcular las fechas de la transacción y vencimiento
            $transactionDate = date('Y-m-d');
            $dueDate = date('Y-m-d', strtotime($transactionDate . ' + ' . $currentAccountSettings->payment_terms . ' days'));

            // Crear la nueva cuenta corriente
            $currentAccount = CurrentAccount::create([
                'client_id' => $data['client_id'],
                'supplier_id' => $data['supplier_id'],
                'currency_id' => $data['currency_id_current_account'], // Asegurarse de que el nombre del campo sea correcto
                'transaction_type' => $data['client_id'] ? TransactionTypeEnum::SALE : TransactionTypeEnum::PURCHASE,
            ]);

            // Crear el crédito inicial
            CurrentAccountInitialCredit::create([
                'total_debit' => $data['total_debit'],
                'description' => 'Crédito Inicial',
                'due_date' => $dueDate,
                'current_account_id' => $currentAccount->id,
                'current_account_settings_id' => $data['current_account_settings_id'],
            ]);

            // Manejo de pagos
            if ($data['partial_payment']) {
                // Si es un pago parcial
                $currentAccount->status = StatusPaymentEnum::PARTIAL;
                $currentAccount->save();

                CurrentAccountPayment::create([
                    'payment_amount' => $data['amount_paid'],
                    'payment_date' => $transactionDate,
                    'payment_method_id' => $data['payment_method_id'],
                    'current_account_id' => $currentAccount->id,
                ]);
            } else {
                // Si no es un pago parcial, establecer el estado como no pagado
                $currentAccount->status = StatusPaymentEnum::UNPAID;
                $currentAccount->save();
            }

            // Confirmar la transacción si todo va bien
            DB::commit();

            return $currentAccount;
        } catch (\Exception $e) {
            dd($e);
            // Si ocurre algún error, revertir la transacción
            DB::rollBack();
            throw new \Exception('Error al crear la cuenta corriente: ' . $e->getMessage());
        }
    }
    /**
     * Actualiza una cuenta corriente existente.
     *
     * @param int $id
     * @param array $data
     * @return CurrentAccount
     */
    public function update(int $id, array $data): CurrentAccount
    {
        // Comenzamos la transacción para asegurar atomicidad
        DB::beginTransaction();

        try {
            // Buscar la cuenta corriente existente
            $currentAccount = CurrentAccount::with(['payments', 'initialCredits'])->findOrFail($id);

            // Obtener las configuraciones de la cuenta corriente
            $currentAccountSettings = CurrentAccountSettings::findOrFail($data['current_account_settings_id']);

            // Calcular la nueva fecha de vencimiento
            $transactionDate = $currentAccount->transaction_date ?? date('Y-m-d'); // Si ya tiene una fecha, conservarla
            $dueDate = date('Y-m-d', strtotime($transactionDate . ' + ' . $currentAccountSettings->payment_terms . ' days'));

            // Actualizar los datos de la cuenta corriente
            $currentAccount->update([
                'client_id' => $data['client_id'],
                'current_account_settings_id' => $data['current_account_settings_id'],
                'currency_id' => $data['currency_id_current_account'],
            ]);

            // get initial credit
            $initialCredit = $currentAccount->initialCredits->filter(function ($initialCredit) {
                return $initialCredit->description === 'Crédito Inicial';
            })->first();

            // Actualizar el crédito inicial
            $initialCredit->update([
                'total_debit' => $data['total_debit'],
                'due_date' => $dueDate,
            ]);

            $totalPaid = $currentAccount->payments->sum('payment_amount');

            if ($totalPaid >= $currentAccount->initialCredits->sum('total_debit')) {
                $currentAccount->status = StatusPaymentEnum::PAID;
            } elseif ($totalPaid > 0) {
                $currentAccount->status = StatusPaymentEnum::PARTIAL;
            } else {
                $currentAccount->status = StatusPaymentEnum::UNPAID;
            }
            $currentAccount->save();
            // Confirmar la transacción si todo va bien
            DB::commit();

            return $currentAccount;

        } catch (\Exception $e) {
            // Si ocurre algún error, revertir la transacción
            DB::rollBack();
            throw new \Exception('Error al actualizar la cuenta corriente: ' . $e->getMessage());
        }
    }

    /**
     * Elimina una cuenta corriente específica.
     *
     * @param int $id
     * @return bool
     */
    public function destroy(int $id): bool
    {
        try {
            $currentAccount = $this->getCurrentAccountById($id);

            // Eliminar los pagos asociados a los créditos iniciales
            foreach ($currentAccount->payments as $payment) {
                $payment->delete();
            }
            // Eliminar los créditos iniciales
            foreach ($currentAccount->initialCredits as $initialCredit) {
                $initialCredit->delete();
            }
            // Eliminar la cuenta corriente
            $currentAccount->delete();
            return true;
        } catch (\Exception $e) {
            throw new \Exception('Error al eliminar la cuenta corriente: ' . $e->getMessage());
        }
    }

    /**
     * Elimina múltiples cuentas corrientes.
     *
     * @param array $ids
     * @return int
     */
    public function deleteMultiple(array $ids): int
    {
        // return CurrentAccount::whereIn('id', $ids)->delete();
        try {
            $currentAccounts = CurrentAccount::whereIn('id', $ids)->get();

            foreach ($currentAccounts as $currentAccount) {
                // Eliminar los pagos asociados a los créditos iniciales
                foreach ($currentAccount->payments as $payment) {
                    $payment->delete();
                }

                // Eliminar los créditos iniciales
                foreach ($currentAccount->initialCredits as $initialCredit) {
                    $initialCredit->delete();
                }
            }

            return CurrentAccount::whereIn('id', $ids)->delete();
        } catch (\Exception $e) {
            throw new \Exception('Error al eliminar las cuentas corrientes: ' . $e->getMessage());
        }
    }

    /**
     * Obtiene las cuentas corrientes para la DataTable.
     *
     * @param Request $request
     * @return mixed
     */
    public function getCurrentAccountsForDataTable($request): mixed
    {
        $query = CurrentAccount::select([
            'current_accounts.id',
            'current_accounts.transaction_type',
            'current_accounts.status',
            DB::raw('(SELECT SUM(initial_credits.total_debit) FROM current_account_initial_credits AS initial_credits WHERE initial_credits.current_account_id = current_accounts.id AND initial_credits.deleted_at IS NULL) as total_debit'),
            DB::raw('(SELECT MAX(initial_credits.due_date) FROM current_account_initial_credits AS initial_credits WHERE initial_credits.current_account_id = current_accounts.id AND initial_credits.deleted_at IS NULL) as due_date'),
            DB::raw('(SELECT SUM(payments.payment_amount) FROM current_account_payments AS payments WHERE payments.current_account_id = current_accounts.id AND payments.deleted_at IS NULL) as total_payment_amount'),
            'clients.name as client_name',
            'suppliers.name as supplier_name',
            'currencies.code as currency_code',
            'currencies.symbol as symbol',
        ])
            ->leftJoin('clients', 'current_accounts.client_id', '=', 'clients.id')
            ->leftJoin('suppliers', 'current_accounts.supplier_id', '=', 'suppliers.id')
            ->leftJoin('currencies', 'current_accounts.currency_id', '=', 'currencies.id')
            ->orderBy('current_accounts.created_at', 'desc')
            ->whereNull('current_accounts.deleted_at');

        // Filtrar por rango de fechas
        if (Helpers::validateDate($request->input('start_date')) && Helpers::validateDate($request->input('end_date'))) {
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $query->whereBetween('current_accounts.created_at', [$startDate, $endDate]);
        }

        $dataTable = DataTables::of($query)->make(true);

        return $dataTable;
    }

    /**
     * Obtiene los pagos de una cuenta corriente para la DataTable.
     *
     * @param int $accountId
     * @return mixed
     */
    public function getAccountPaymentsForDataTable(int $accountId): mixed
    {
        $query = CurrentAccountPayment::where('current_account_id', $accountId)
            ->select(['payment_amount', 'payment_date', 'payment_method_id']);

        return datatables()->eloquent($query)->make(true);
    }

    /**
     * Actualiza el estado de pago de una cuenta corriente.
     *
     * @param int $accountId
     * @param string $paymentStatus
     * @return CurrentAccount
     */
    public function updatePaymentStatus(int $accountId, string $paymentStatus): CurrentAccount
    {
        $currentAccount = $this->getCurrentAccountById($accountId);
        $currentAccount->status = $paymentStatus;
        $currentAccount->save();

        return $currentAccount;
    }

    /**
     * Obtiene todos los metodos de pago.
     *
     * @return Collection
     */
    public function getPaymentMethods(): Collection
    {
        return PaymentMethod::all();
    }

    public function getClients(): Collection
    {
        return Client::all();
    }

    public function getSuppliers()
    {
        return Supplier::all();
    }

    public function getCurrentAccountSettings()
    {
        return CurrentAccountSettings::all();
    }

    public function getCurrency()
    {
        return Currency::all();
    }

    public function getCurrentAccountStatus()
    {
        return StatusPaymentEnum::cases();
    }

    public function getCurrentAccountsForExport($entityType, $clientId, $supplierId, $status, $startDate, $endDate)
    {
        $query = CurrentAccount::with(['client', 'supplier', 'initialCredits', 'payments']);
    
        // Filtrar por tipo de entidad (cliente o proveedor)
        if ($entityType == 'client') {
            $query->whereNotNull('client_id');
            if ($clientId) {
                $query->whereHas('client', function ($q) use ($clientId) {
                    $q->where('name', $clientId);
                });
            }
        } elseif ($entityType == 'supplier') {
            $query->whereNotNull('supplier_id');
            if ($supplierId) {
                $query->whereHas('supplier', function ($q) use ($supplierId) {
                    $q->where('name', $supplierId);
                });
            }
        }
    
        // Filtrar por estado del pago
        if ($status) {
            $query->where('status', $status);
        }
    
        // Filtrar por rango de fechas
        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }
    
        return $query->get();
    }
}
