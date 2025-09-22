<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CompanyResource;
use App\Models\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    /**
     * GET /api/companies
     * Query params:
     *  - search (opcional): filtra por nombre
     *  - per_page (opcional): tamaño de página (default 20)
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 20);
        $search  = trim((string) $request->input('search', ''));

        $query = Company::query();

        if ($search !== '') {
            $query->where('name', 'like', "%{$search}%");
        }

        $companies = $query->orderBy('name', 'asc')->paginate($perPage);

        return CompanyResource::collection($companies);
    }

    /**
     * GET /api/companies/{company}
     */
    public function show(Company $company)
    {
        return new CompanyResource($company);
    }
}
