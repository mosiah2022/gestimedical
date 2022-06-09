<?php

namespace App\Exports;

use App\Models\PatientLmDetail;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use \Maatwebsite\Excel\Sheet;


class OrderExport extends \PhpOffice\PhpSpreadsheet\Cell\StringValueBinder implements  FromView, ShouldAutoSize, WithStyles, WithCustomValueBinder
{

    public function styles(Worksheet $sheet)
    {
        $styleArray = [
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'outline' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                    'color' => ['argb' => '00000000'],
                ],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'argb' => 'f0f0f0',
                ],
                'endColor' => [
                    'argb' => 'f0f0f0',
                ],
            ],
            'font' => [
                'bold' => true,
            ],
        ];
        $styleAlign = [
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ];
        $sheet->getStyle('A2:G2')->applyFromArray($styleArray);
        $sheet->getStyle('A3:G3')->applyFromArray($styleArray);
        $sheet->getStyle('A4:G4')->applyFromArray($styleArray);
        $sheet->getStyle('C')->applyFromArray($styleAlign);
    }

    public function view(): View
    {
        return view('patients.orders', [
            'orders' => PatientLmDetail::with(['product', 'order', 'patient'])->get()->groupBy('order.id')
        ]);
    }
}
