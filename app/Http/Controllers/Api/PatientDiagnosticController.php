<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PatientDiagnostic\PatientDiagnosticAdd as PatientDiagnosticRequest;
use App\Http\Requests\PatientDiagnostic\PatientDiagnosticUpdate as PatientDiagnosticUpdateRequest;
use App\Http\Resources\PatientDiagnostic as PatientDiagnosticResource;
use App\Http\Resources\PatientDiagnosticCollection;
use App\Models\PatientDiagnostic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PatientDiagnosticController extends Controller
{
    protected $patient_diagnostic;

    public function __construct(PatientDiagnostic $patient_diagnostic) {
        $this->patient_diagnostic = $patient_diagnostic;
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $companyId = intval(session('company'));
        $perPage = $request->input('perPage', 10);
        $currentPage = $request->input('page', 1);
        $globalFilter = $request->input('filters', '');

        $query = PatientDiagnostic::where('company_id', $companyId)
        ->whereHas('patient', function($q) use ($globalFilter) {
            if ($globalFilter) {
                $q->where('first_name', 'like', '%' . $globalFilter . '%');
            }
        })
        ->with('patient');

        $totalRecords = $query->count();

        $diagnostics = $query->orderBy('description', 'asc')->paginate($perPage, ['*'], 'page', $currentPage);
        $totalPages = ceil($totalRecords / $perPage);

        return response()->json([
            'diagnostics' => new PatientDiagnosticCollection($diagnostics),
            'totalRecords' => $totalRecords,
            'totalPages' => $totalPages,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  PatientDiagnosticRequest  $request
     * @return JsonResponse
     */
    public function store(PatientDiagnosticRequest $request)
    {
        $request->merge(['company_id' => intval(session('company'))]);
        $diagnostics = $this->patient_diagnostic->create($request->all());
        return response()->json(new PatientDiagnosticResource($diagnostics), 200);
    }


    /**
     * Display the specified resource.
     *
     * @param PatientDiagnostic $patient_diagnostic
     * @return JsonResponse
     */
    public function show(PatientDiagnostic $patient_diagnostic): JsonResponse
    {
        return response()->json(
            new PatientDiagnosticResource($patient_diagnostic)
        );
    }

    public function getDiagnostics($id) {
        $patient = PatientDiagnostic::where('patient_id', $id)->get();

        return response()->json(
            $patient
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  PatientDiagnostic $patient_diagnostic
     * @return JsonResponse
     */
    public function destroy(PatientDiagnostic $patient_diagnostic): JsonResponse
    {
        $patient_diagnostic->delete();
        return response()->json(null, 204);
    }

    public function update_diagnostic(PatientDiagnosticUpdateRequest $request, $id){
        $update_diagnostic = PatientDiagnostic::where('id', $id)
                                                ->update(['description' => $request->description]);
        return response()->json("Se actualizo correctamente el diagnostico");
    }
}
