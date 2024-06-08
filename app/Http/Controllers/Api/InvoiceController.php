<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\PatientLm;

//resource and request
use App\Http\Resources\Invoice as InvoiceResource;
use App\Http\Resources\InvoiceCollection;
use App\Http\Requests\Invoice\Invoice as InvoiceRequest;
use App\Http\Requests\Invoice\InvoiceUpdate;
use Illuminate\Http\JsonResponse;

class InvoiceController extends Controller
{
    protected $invoice;

    public function __construct(Invoice $invoice){
        $this->invoice = $invoice;
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return response()->json(
            new InvoiceCollection(
                $this->invoice
                ->where('company_id', intval(session('company')))
                ->orderBy('id','desc')
                ->get()
            )
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  Invoice $invoice
     * @return JsonResponse
     */
    public function show(Invoice $invoice) : JsonResponse
    {
        return response()->json(
            new InvoiceResource($invoice)
        );
        
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  InvoiceRequest
     * @param  Invoice $invoice
     * @return JsonResponse
     */
    public function update(InvoiceUpdate $request, Invoice $invoice): JsonResponse
    {
        $invoice_number = Invoice::where('id',$invoice->id)->first()->invoice_number;

        //particular case change de invoice_number on PatientLm
        //becouse the relationship is with the number directly
        //maybe not is a good practice but maybe i gonna change soomly
        PatientLm::where('invoice_number',$invoice_number)->update(['invoice_number'=>$request->invoice_number]);
        $invoice->update($request->all());
        return response()->json(new InvoiceResource($invoice));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}