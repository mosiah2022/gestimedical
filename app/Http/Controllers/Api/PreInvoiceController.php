<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\PatientLm;
use App\Models\PreInvoice;

use App\Http\Requests\Invoice\Invoice as InvoiceRequest;
use App\Http\Requests\PreInvoice\PreInvoice as PreInvoiceRequest;
use App\Http\Resources\PreInvoice as PreInvoiceResource;
use App\Http\Resources\PreInvoiceCollection;
use http\Env\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class PreInvoiceController extends Controller
{
    protected $pre_invoice;

    public function __construct(PreInvoice $pre_invoice) {
        $this->pre_invoice = $pre_invoice;
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return response()->json(
            new PreInvoiceCollection(
                $this->pre_invoice->orderBy('id','desc')->get()
            )
        );
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param PreInvoiceRequest $request
     * @return JsonResponse
     */
    public function store(PreInvoiceRequest $request): JsonResponse
    {
        $pre_invoice = $this->pre_invoice->create($request->all());
        return response()->json(
            new PreInvoiceResource($pre_invoice), 201
        );
    }

    public function getCountPreInvoices($id) {
        $pre_invoice = PreInvoice::where('status', 'proccess')->sum('total_items');
        return response()->json($pre_invoice+$id);
    }

    public function getInvoiceActive() {
        $pre_invoice = PreInvoice::where('status', 'proccess')->get();
        return response()->json(
            new PreInvoiceCollection($pre_invoice)
        );
    }

    public function update_preinvoice(InvoiceRequest $request) {
        
        $invoice = new Invoice();
        $request->merge(['company_id' => intval(session('company'))]);
        $invoice->invoice_number = $request->invoice_number;
        $invoice->create($request->all());

        return $this->update_invoice($request->invoice_number);
    }

    public function update_invoice($id){

        $update = DB::table('pre_invoices')
            ->join('patient_lms','patient_lms.id','=','pre_invoices.patient_lms_id')
            ->where('pre_invoices.status','proccess')
            ->update(['patient_lms.invoice_number' => $id,
                'patient_lms.status' => 'completed',
                'pre_invoices.status' => 'completed']);

        return response()->json("Actualizado");
    }
}
