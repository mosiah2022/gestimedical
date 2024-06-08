<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PatientAddress as PatientAddressResource;
use App\Http\Resources\PatientAddressCollection;
use App\Http\Requests\PatientAddress\PatientAddressAdd as PatientAddressRequest;
use App\Models\PatientAdress;
use Illuminate\Http\JsonResponse;

class PatientAddressController extends Controller
{
    protected $patient_address;

    public function __construct(PatientAdress $patient_address){
        $this->patient_address = $patient_address;
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return response()->json(
            new PatientAddressCollection(
                $this->patient_address
                ->where('company_id',  intval(session('company')))
                ->orderBy('patient_id', 'desc')
                ->get()
            )
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  PatientAddressRequest  $request
     * @return JsonResponse
     */
    public function store(PatientAddressRequest $request)
    {
        $request->merge(['company_id' => intval(session('company'))]);
        $patient = $this->patient_address->create($request->all());
        return response()->json(new PatientAddressResource($patient), 200);
    }

    /**
     * Display the specified resource.
     *
     * @param PatientAdress $patient_address
     * @return JsonResponse
     */
    public function show(PatientAdress $patient_address): JsonResponse
    {
        return response()->json(
            new PatientAddressResource($patient_address)
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param PatientAdress $patient_address
     * @return JsonResponse
     */
    public function destroy(PatientAdress $patient_address): JsonResponse
    {
        $patient_address->delete();
        return response()->json(null, 204);
    }

    public function getAddress($id, $category){
        $patient = PatientAdress::where('patient_id',$id)
                                 ->where('category', $category)
                                 ->get();
        return response()->json($patient);
    }

}
