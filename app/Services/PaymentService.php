<?php

namespace App\Services;

abstract class PaymentService
{
    /**
     * Procesa la transacción con el POS correspondiente.
     *
     * @param array $transactionData
     * @return mixed
     */
    abstract public function process(array $transactionData);

    /**
     * Método opcional para formatear datos.
     *
     * @param array $data
     * @return array
     */
    protected function formatData(array $data): array
    {
        return $data; // Puede ser sobreescrito por servicios específicos.
    }
}
