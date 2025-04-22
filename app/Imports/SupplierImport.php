<?php

namespace App\Imports;

use App\Models\Supplier;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\Log;

class SupplierImport implements ToModel, WithHeadingRow, WithValidation
{
    protected $store_id;

    public function __construct($store_id)
    {
        $this->store_id = $store_id;
    }

    public function model(array $row)
    {
        try {
            // Traducir los tipos de documento
            $docTypeTranslations = [
                'CI' => 'CI',
                'RUT' => 'RUT',
                'PASAPORTE' => 'PASSPORT',
                'OTRO' => 'OTHER'
            ];

            // Traducir los métodos de pago
            $paymentMethodTranslations = [
                'Efectivo' => 'cash',
                'Crédito' => 'credit',
                'Débito' => 'debit',
                'Cheque' => 'check'
            ];

            return new Supplier([
                'store_id' => $this->store_id,
                'doc_type' => $docTypeTranslations[$row['tipo_documento']],
                'doc_number' => $row['numero_documento'],
                'name' => $row['nombre'],
                'email' => $row['email'],
                'phone' => $row['telefono'],
                'address' => $row['direccion'],
                'city' => $row['ciudad'],
                'state' => $row['departamento'],
                'country' => $row['pais'],
                'default_payment_method' => $paymentMethodTranslations[$row['metodo_de_pago']],
            ]);
        } catch (\Exception $e) {
            Log::error('Error al importar fila: ' . json_encode($row) . ' - ' . $e->getMessage());
            throw $e;
        }
    }

    public function rules(): array
    {
        return [
            'tipo_documento' => 'required|in:CI,RUT,PASAPORTE,OTRO',
            'numero_documento' => 'required|numeric',
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'telefono' => 'required|max:255',
            'direccion' => 'required|string|max:255',
            'ciudad' => 'required|string|max:255',
            'departamento' => 'required|string|max:255',
            'pais' => 'required|string|max:255',
            'metodo_de_pago' => 'required|in:Efectivo,Crédito,Débito,Cheque',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'tipo_documento.required' => 'El tipo de documento es obligatorio',
            'tipo_documento.in' => 'El tipo de documento debe ser CI, RUT, PASAPORTE u OTRO',
            'numero_documento.required' => 'El número de documento es obligatorio',
            'numero_documento.numeric' => 'El número de documento debe ser numérico',
            'nombre.required' => 'El nombre es obligatorio',
            'email.required' => 'El email es obligatorio',
            'email.email' => 'El email debe ser válido',
            'telefono.required' => 'El teléfono es obligatorio',
            'direccion.required' => 'La dirección es obligatoria',
            'ciudad.required' => 'La ciudad es obligatoria',
            'departamento.required' => 'El departamento es obligatorio',
            'pais.required' => 'El país es obligatorio',
            'metodo_de_pago.required' => 'El método de pago es obligatorio',
            'metodo_de_pago.in' => 'El método de pago debe ser Efectivo, Crédito, Débito o Cheque',
        ];
    }
}