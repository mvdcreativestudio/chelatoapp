<?php

namespace App\Http\Controllers;

use App\Factories\PaymentServiceFactory;
use Illuminate\Http\Request;
use Exception;

class PaymentController extends Controller
{
    public function processPayment(Request $request)
    {
        $validatedData = $request->validate([
            'payment_method' => 'required|string',
            // Añadir validaciones específicas para cada campo necesario
            'PosID' => 'required|string',
            'Empresa' => 'required|string',
            'Local' => 'required|string',
            'Caja' => 'required|string',
            'UserId' => 'required|string',
            'TransactionDateTimeyyyyMMddHHmmssSSS' => 'required|string',
            'Amount' => 'required|string',
            'Quotas' => 'required|numeric',
            'Plan' => 'required|integer',
            'Currency' => 'required|string',
            'TaxableAmount' => 'required|string',
            'InvoiceAmount' => 'required|string',
            'TaxAmount' => 'required|string',
            'IVAAmount' => 'required|string',
            'NeedToReadCard' => 'required|boolean',
        ]);

        try {
            // Crear el servicio de pago adecuado basado en el método recibido
            $paymentService = PaymentServiceFactory::create($validatedData['payment_method']);
            $response = $paymentService->process($validatedData);

            return response()->json($response);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
