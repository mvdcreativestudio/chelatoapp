<?php

namespace App\Imports;

use App\Models\Client;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\Log;

class ClientImport implements ToModel, WithHeadingRow, WithValidation
{
    protected $store_id;

    public function __construct($store_id)
    {
        $this->store_id = $store_id;
    }

    public function model(array $row)
    {
        try {
            // Traducir los tipos de cliente
            $typeTranslations = [
                'Empresa' => 'company',
                'Individual' => 'individual',
                'No Cliente' => 'no-client'
            ];

            $type = $typeTranslations[$row['tipo']] ?? null;

            return new Client([
                'store_id' => $this->store_id,
                'type' => $type,
                'name' => $row['nombre'], // Nombre*
                'lastname' => $row['apellido'] ?? null, // Apellido
                'email' => $row['email'], // Email*
                'phone' => $row['telefono'] ?? null, // Teléfono
                'ci' => $row['cedula'] ?? null, // Cédula
                'passport' => $row['pasaporte'] ?? null, // Pasaporte
                'rut' => $row['rut'] ?? null, // RUT
                'company_name' => $row['razon_social'] ?? null, // Razón Social
                'address' => $row['direccion'] ?? null, // Dirección
                'city' => $row['ciudad'] ?? null, // Ciudad
                'state' => $row['departamento'] ?? null, // Departamento
                'country' => $row['pais'] ?? null, // País
                'website' => $row['sitio_web'] ?? null, // Sitio Web
            ]);
        } catch (\Exception $e) {
            Log::error('Error al importar fila: ' . json_encode($row) . ' - ' . $e->getMessage());
            throw $e;
        }
    }

    public function rules(): array
    {
        return [
            'tipo' => 'required|in:Empresa,Individual,No Cliente',
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'cedula' => 'nullable|max:20',
            'pasaporte' => 'nullable|max:20',
            'rut' => 'nullable|max:20',
            'telefono' => 'nullable|max:20',
            'razon_social' => 'nullable|string|max:255',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'tipo.required' => 'El tipo de cliente es obligatorio',
            'tipo.in' => 'El tipo debe ser Empresa, Individual o No Cliente',
            'nombre.required' => 'El nombre es obligatorio',
            'email.required' => 'El email es obligatorio',
            'email.email' => 'El email debe ser válido',
        ];
    }
}