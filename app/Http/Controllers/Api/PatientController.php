<?php

namespace App\Http\Controllers\Api;

use App\Models\Patient;
use App\Models\Product;
use App\Exports\PatientExport;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;

/* Extern Libraries */
use App\Http\Resources\PatientCollection;
use App\Http\Requests\Patients\PatientUpdate;
use App\Http\Resources\Patient as PatientResource;
use App\Http\Requests\Patients\Patient as PatientRequest;


class PatientController extends Controller
{
    protected $patient;

    public function __construct(Patient $patient) {
        $this->patient = $patient;
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return response()->json(
            new PatientCollection($this->patient
                    ->where('company_id', intval(session('company')))
                    ->orderBy('first_name', 'asc')
                    ->get()
            )
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param ProductRequest $request
     * @return JsonResponse
     */
    public function store(PatientRequest $request): JsonResponse
    {
        $request->merge(['company_id' => intval(session('company'))]);
        $patient = $this->patient->create($request->all());
        return response()->json(new PatientResource($patient), 201);
    }

    /**
     * Display the specified resource.
     *
     * @param Product $product
     * @return JsonResponse
     */
    public function show(Patient $patient): JsonResponse
    {
        return response()->json(
            new PatientResource($patient)
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param PatientRequest $request
     * @param Patient $patient
     * @return JsonResponse
     */
//    public function update(ProductRequest $request, Product $product): JsonResponse
//    {
//        $product->update($request->all());
//        return response()->json(new ProductResource($product));
//    }

    public function update(PatientUpdate $request, Patient $patient): JsonResponse
    {
        $patient->update($request->all());
        return response()->json(new PatientResource($patient));
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param Product $product
     * @return JsonResponse
     */
    public function destroy(Product $product): JsonResponse
    {
        $product->delete();
        return response()->json(null, 204);
    }

    public function getPatientsByDate(Patient $patient, $dateIni, $dateEnd) {
        $patients = Patient::orderBy('id','asc')
            ->patientbydate($dateIni,$dateEnd)
            ->get();

        return response()->json(new PatientCollection($patients));
    }

    public function export($iniDate,$endDate){
        return Excel::download(new PatientExport($iniDate, $endDate), 'patients.xlsx');
    }
}
