<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\PatientLm;
use App\Models\PatientLmDetail;
use App\Models\PreInvoice;
use Illuminate\Http\Response;
use App\Http\Resources\PatientLm as PatientLmResource;
use App\Http\Resources\PatientLmCollection;
use App\Http\Requests\Patients\PatientLmRequest;
use App\Http\Requests\PatientLm\PatientLmUpdate;
use App\Http\Requests\PatientLm\PatientLmOrder;
use App\Http\Requests\Patients\PatientLmUpdateStatus;

use App\Exports\OrderExport;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;

class PatientLmController extends Controller
{
    protected $patient_lm;

    public function __construct(PatientLm $patient_lm){
        $this->patient_lm = $patient_lm;
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return response()->json(
            new PatientLmCollection(
                $this->patient_lm
                ->where('company_id', intval(session('company')))
                ->orderBy('id','desc')
                ->get()
            )
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     */
    public function store(PatientLmRequest $request)
    {
        $request->merge(['company_id' => intval(session('company'))]);
        $request->merge(['date_ini' => Carbon::parse($request->date_ini)->toDateString()]);
        $request->merge(['date_end' => Carbon::parse($request->date_end)->toDateString()]);

        $patient_lm = $this->patient_lm->create($request->all());
        return response()->json(new PatientLmResource($patient_lm), 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show(PatientLm $patient_lm)
    {
        $patient_lm = PatientLm::where('id',$patient_lm->id)
                                ->with(['patient','orders'])
                                ->first();
        return response()->json(
            new PatientLmResource($patient_lm)
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param  int  $id
     * @return Response
     */
    public function update(PatientLmUpdate $request, PatientLm $patient_lm)
    {
        $request->merge(['date_ini' => Carbon::parse($request->date_ini)->toDateString()]);
        $request->merge(['date_end' => Carbon::parse($request->date_end)->toDateString()]);

        $patient_lm->update($request->all());
        return response()->json(new PatientLmResource($patient_lm));
    }

    /**
     * Update order the specified resource in storage.
     *
     * @param PatientLmUpdate $request
     * @param int $id
     * @return JsonResponse
     */
    public function update_order(PatientLmOrder $request, int $id): JsonResponse
    {
        $patient_lm = PatientLm::where('id', $id)->update([
            'lm_code' => $request->lm_code,
            'authorized_by' => $request->authorized_by,
            'observation'   => $request->observation
        ]);

        return response()->json($patient_lm);
    }

    public function update_lmcode(PatientLmUpdateStatus $request, $id) {
        $patient_lm = PatientLm::where('lm_code', $id)->update([
            'status' => $request->status
        ]);
        return response()->json("Se a cambiando el status exitosamente");
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy(PatientLm $patient_lm)
    {
        $patientDetails  = PatientLmDetail::where('order_id',$patient_lm->id)->count();
        $lmcode          = PatientLm::select('lm_code')->where('id',$patient_lm->id)->first();
        $preinvoice      = PreInvoice::where('lm_code',$lmcode['lm_code'])->count();

        if($preinvoice > 0 && $patientDetails > 0) {
            PreInvoice::where('lm_code',$lmcode['lm_code'])->delete();
            PatientLmDetail::where('order_id',$patient_lm->id)->delete();
            $patient_lm->delete();
            return response()->json(null, 204);
        }elseif($preinvoice > 0){
            PreInvoice::where('lm_code',$lmcode['lm_code'])->delete();
            $patient_lm->delete();
            return response()->json(null, 204);
        }elseif($patientDetails > 0){
            PatientLmDetail::where('order_id',$patient_lm->id)->delete();
            $patient_lm->delete();
            return response()->json(null, 204);
        }else{
            $patient_lm->delete();
            return response()->json(null, 204);
        }
    }

    public function export($id) {
        return Excel::download(new OrderExport($id), $id.'_orders.xlsx');
    }

    //Metodo para encontrar ordenes de pacientes o pacientes
    public function findOrders($id) {
        $find_orders = DB::table('patient_lms')
                        ->join('patients', 'patient_lms.patient_id','=','patients.id')
                        ->select('patients.id as patient_id','patients.personal_id', 'patients.first_name', 'patients.last_name', 'patient_lms.lm_code','patient_lms.id')
                        ->where('patient_lms.lm_code',$id)
                        ->where('patient_lms.company_id', intval(session('company')))
                        ->orWhere('patients.personal_id',$id)
                        ->orWhere('patients.first_name','like',$id.'%')
                        ->orWhere('patients.last_name','like',$id.'%')
                        ->get();
        return response()->json($find_orders);
    }

    public function getOrdersLm($dateini, $dateend){
        $getLm = DB::table('patient_lms')
                          ->join('patient_lm_details', 'patient_lms.id','=','patient_lm_details.order_id')
                          ->select('patient_lms.lm_code','patient_lms.date_ini',DB::raw('count(*) as total_detail'))
                          ->where('status','pending')
                          ->where('patient_lms.company_id', intval(session('company')))
                          ->whereBetween('patient_lms.date_ini', [$dateini,$dateend])
                          ->groupBy('patient_lms.lm_code','patient_lms.date_ini')
                          ->get();

        /*
            To use the resource but dosent work with this type relationship
            return response()->json(
            new PatientLmCollection($getLms));
        */

        return response()->json($getLm);
    }

    public function getOrdersCheck() {
        $getOrders = PatientLm::where('status', 'proccess')->where('company_id', intval(session('company')))->get();

        if(count($getOrders)>0) {
            return response()->json(
                new PatientLmCollection($getOrders)
            );
        }else{
            return false;
        }
    }

    public function patientByLm($id) {
        $patient_lm = PatientLm::where('lm_code',$id)->where('company_id', intval(session('company')))->get();

        return response()->json(
            new PatientLmCollection($patient_lm)
        );
    }

    public function getLmInfo($id) {
        $patientLm = PatientLm::where('lm_code',$id)->first();
        return $patientLm->lm_code;
    }

}
