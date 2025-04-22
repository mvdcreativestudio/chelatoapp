<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

class ClientTemplateImport implements FromArray, WithHeadings, WithStyles, WithColumnWidths
{
    public function array(): array
    {
        return [
            [
                'Empresa', // Valor por defecto para tipo
                '', // Resto de campos vacíos
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                ''
            ]
        ];
    }

    public function headings(): array
    {
        return [
            'Tipo*',
            'Nombre*',
            'Apellido',
            'Email*',
            'Teléfono',
            'Cédula',
            'Pasaporte',
            'RUT',
            'Razón Social',
            'Dirección',
            'Ciudad',
            'Departamento',
            'País',
            'Sitio Web'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Estilo para los encabezados
        $sheet->getStyle('A1:N1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => '000000'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E4E4E4']
            ]
        ]);

        // Lista desplegable para el tipo de cliente
        $typeValidation = $sheet->getCell('A2')->getDataValidation();
        $typeValidation->setType(DataValidation::TYPE_LIST)
            ->setErrorStyle(DataValidation::STYLE_INFORMATION)
            ->setAllowBlank(false)
            ->setShowInputMessage(true)
            ->setShowErrorMessage(true)
            ->setShowDropDown(true)
            ->setErrorTitle('Error')
            ->setError('El valor no está en la lista')
            ->setPromptTitle('Seleccione')
            ->setPrompt('Seleccione el tipo de cliente')
            ->setFormula1('"Empresa,Individual,No Cliente"');

        // Aplicar la validación a todas las celdas de la columna A
        for ($i = 2; $i <= 1000; $i++) {
            $sheet->getCell("A$i")->setDataValidation(clone $typeValidation);
        }

        return [
            1 => ['font' => ['bold' => true]]
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15, // Tipo
            'B' => 25, // Nombre
            'C' => 25, // Apellido
            'D' => 35, // Email
            'E' => 15, // Teléfono
            'F' => 15, // Cédula
            'G' => 15, // Pasaporte
            'H' => 15, // RUT
            'I' => 30, // Razón Social
            'J' => 40, // Dirección
            'K' => 20, // Ciudad
            'L' => 20, // Departamento
            'M' => 20, // País
            'N' => 30, // Sitio Web
        ];
    }
}