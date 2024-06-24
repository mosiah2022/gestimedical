<?php

namespace App\Exports;

use App\Models\patient_lm_details;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromView;

class ValueExport implements FromView
{
    protected $dateini;
    protected $dateend;
    protected $companyId;

    public function __construct($dateini, $dateend, $companyId) {
        $this->dateini = $dateini;
        $this->dateend = $dateend;
        $this->companyId = $companyId;
    }

    /**
     * @return View
     */
    public function view():View
    {
        $dateini = $this->dateini;
        $dateend = $this->dateend;

        $query = DB::select(DB::raw('select p.first_name, p.last_name, p.personal_id,  pl.lm_code,  SUM(p2.price * pld.prescription) as total, pl.id
from patient_lm_details pld
INNER JOIN patients p on p.id = pld.patient_id
INNER JOIN patient_lms pl on pld.order_id = pl.id
INNER JOIN products p2 on pld.product_id = p2.id
where pl.lm_code <> "" and pl.invoice_number IS NOT NULL
and pl.date_ini BETWEEN :dini and :dateend
and p.company_id = :company
GROUP BY p.first_name, p.last_name, p.personal_id, pl.lm_code, pl.id
ORDER BY pl.invoice_number, pl.id '),['dini'=>$dateini, 'dateend' => $dateend, 'company' => $this->companyId]);

        return view('report_values.values', [
            'patients' => $query
        ]);
    }
}
