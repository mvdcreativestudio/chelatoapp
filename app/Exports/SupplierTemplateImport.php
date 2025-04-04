<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

class SupplierTemplateImport implements FromArray, WithHeadings, WithStyles, WithColumnWidths
{
    public function array(): array
    {
        return [
            [
                'CI', // Valor por defecto para doc_type
                '', // Resto de campos vacíos
                '', 
                '',
                '',
                '',
                '',
                '',
                '',
                'Efectivo', // Valor por defecto para método de pago
            ]
        ];
    }

    public function headings(): array
    {
        return [
            'Tipo Documento*',
            'Número Documento*',
            'Nombre*',
            'Email*',
            'Teléfono*',
            'Dirección*',
            'Ciudad*',
            'Departamento*',
            'País*',
            'Método de Pago*'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Estilo para los encabezados
        $sheet->getStyle('A1:J1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => '000000'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E4E4E4']
            ]
        ]);

        // Lista desplegable para el tipo de documento
        $docTypeValidation = $sheet->getCell('A2')->getDataValidation();
        $docTypeValidation->setType(DataValidation::TYPE_LIST)
            ->setErrorStyle(DataValidation::STYLE_INFORMATION)
            ->setAllowBlank(false)
            ->setShowInputMessage(true)
            ->setShowErrorMessage(true)
            ->setShowDropDown(true)
            ->setErrorTitle('Error')
            ->setError('El valor no está en la lista')
            ->setPromptTitle('Seleccione')
            ->setPrompt('Seleccione el tipo de documento')
            ->setFormula1('"CI,RUT,PASAPORTE,OTRO"');

        // Lista desplegable para el método de pago
        $paymentMethodValidation = $sheet->getCell('J2')->getDataValidation();
        $paymentMethodValidation->setType(DataValidation::TYPE_LIST)
            ->setErrorStyle(DataValidation::STYLE_INFORMATION)
            ->setAllowBlank(false)
            ->setShowInputMessage(true)
            ->setShowErrorMessage(true)
            ->setShowDropDown(true)
            ->setErrorTitle('Error')
            ->setError('El valor no está en la lista')
            ->setPromptTitle('Seleccione')
            ->setPrompt('Seleccione el método de pago')
            ->setFormula1('"Efectivo,Crédito,Débito,Cheque"');

        // Aplicar la validación a todas las celdas
        for ($i = 2; $i <= 1000; $i++) {
            $sheet->getCell("A$i")->setDataValidation(clone $docTypeValidation);
            $sheet->getCell("J$i")->setDataValidation(clone $paymentMethodValidation);
        }

        return [
            1 => ['font' => ['bold' => true]]
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20, // Tipo Documento
            'B' => 20, // Número Documento
            'C' => 30, // Nombre
            'D' => 35, // Email
            'E' => 15, // Teléfono
            'F' => 40, // Dirección
            'G' => 20, // Ciudad
            'H' => 20, // Departamento
            'I' => 20, // País
            'J' => 25, // Método de Pago
        ];
    }
}