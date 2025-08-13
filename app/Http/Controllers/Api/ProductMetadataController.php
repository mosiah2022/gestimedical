<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\ProductMetadata;
use App\Http\Controllers\Controller;
use App\Models\CompanyProductMetadata;
use App\Http\Resources\ProductMetadataResource;

class ProductMetadataController extends Controller
{
    public function index(Request $request)
    {
        $query = ProductMetadata::query();

        $companyId = intval(session('company'));

        if ($request->filled('product_id')) {
            $productId = $request->get('product_id');

            $relatedIds = CompanyProductMetadata::where('product_id', $productId)
                ->where('company_id', $companyId)
                ->pluck('product_metadata_id');

            if ($relatedIds->isNotEmpty()) {
                $query->whereIn('id', $relatedIds);
            }
        }

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('code', 'LIKE', "%$search%")
                ->orWhere('name', 'LIKE', "%$search%");
            });
        }

        return ProductMetadataResource::collection(
            $query->paginate($request->get('per_page', 100))
        );
    }
}
