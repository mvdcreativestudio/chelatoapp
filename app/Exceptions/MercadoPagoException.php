<?php

namespace App\Exceptions;

use Exception;

class MercadoPagoException extends Exception
{
    protected $details;

    public function __construct(string $message, array $details = [])
    {
        parent::__construct($message);
        $this->details = $details;
    }

    /**
     * Obtener los detalles del error.
     */
    public function getDetails(): array
    {
        return $this->details;
    }
}
