<?php

namespace App\Factories;

use App\Services\ScanntechService;
use App\Services\VisaNetService;
use App\Services\GetnetService;
use Exception;

class PaymentServiceFactory
{
    public static function create($paymentMethod)
    {
        switch ($paymentMethod) {
            case 'scanntech':
                return new ScanntechService();
            // case 'visanet':
            //     return new VisaNetService();
            // case 'getnet':
            //     return new GetnetService();
            default:
                throw new Exception('Método de pago no soportado.');
        }
    }
}
