<?php

namespace App\Exports;

use App\Models\Invoice;
use App\Models\PatientLmDetail;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use Maatwebsite\Excel\Concerns\ToModel;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use \Maatwebsite\Excel\Sheet;


class OrderExport extends DefaultValueBinder implements  FromView, ShouldAutoSize, WithStyles, WithCustomValueBinder
{
    public $invoice;
    public $idCompany;

    public function bindValue(Cell $cell, $value)
    {
        if (is_numeric($value)) {
            $cell->setValueExplicit($value, DataType::TYPE_NUMERIC);

            return true;
        }

        // else return default behavior
        return parent::bindValue($cell, $value);
    }

    public function __construct(int $invoice){
        $this->invoice = $invoice;
    }

    public function view(): View
    {
        $query = PatientLmDetail::with(['product', 'patient','product.presentation'])->whereHas('order', function($query){
            return $query->where('invoice_number',$this->invoice);
        })->get()->groupBy('order.id');

        $globalDiscount = 0;

        $total = $query->map(function($order) use (&$globalDiscount) {
            $orderTotal = array_reduce(
                $order->toArray(),
                function ($sum, $patient) {
                    return $sum + ((float) $patient['price_detail'] * (float) $patient['prescription']);
                },
                0
            );

            $globalDiscount += (float) $order->first()['order']['discount_percent'];

            return $orderTotal;
        })->reduce(function($a, $b) {
            return $a + $b;
        });


        $getCompany = Invoice::where('invoice_number', $this->invoice)->with(['company'])->first();
        $nameCompany = $getCompany->company->name;
        $this->idCompany = $getCompany->company->id;

        return view('patients.orders', [
            'orders' => $query,
            'invoice_number' => $this->invoice,
            'company' => $nameCompany,
            'companyId' => $this->idCompany,
            'total' => $total,
            'globalDiscount' => $globalDiscount
        ]);
    }

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
        if ($this->idCompany === 1) {
            $sheet->getStyle('A2:I2')->applyFromArray($styleArray);
            $sheet->getStyle('A3:I3')->applyFromArray($styleArray);
            $sheet->getStyle('A4:I4')->applyFromArray($styleArray);
        } else {
            $sheet->getStyle('A2:H2')->applyFromArray($styleArray);
            $sheet->getStyle('A3:H3')->applyFromArray($styleArray);
            $sheet->getStyle('A4:H4')->applyFromArray($styleArray);
        }

        $sheet->getStyle('C')->applyFromArray($styleAlign);
    }
}
