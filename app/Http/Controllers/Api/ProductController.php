<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\ProductMetadata;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

use App\Models\CompanyProductMetadata;
use App\Http\Resources\ProductCollection;
use App\Http\Resources\Product as ProductResource;
use App\Http\Requests\Products\Product as ProductRequest;
use App\Http\Requests\Products\ProductUpdate as ProductUpdateRequest;

class ProductController extends Controller
{
    protected $product;

    public function __construct(Product $product) {
        $this->product = $product;
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('perPage', 10);
        $currentPage = $request->input('page', 1);
        $globalFilter = $request->input('filters', '');

        // quitamos el where de company_id
        $query = Product::query();

        if ($globalFilter) {
            $query->where(function ($q) use ($globalFilter) {
                $q->where('name', 'like', '%' . $globalFilter . '%');
            });
        }

        $totalRecords = $query->count();

        $products = $query->orderBy('name', 'asc')
                        ->paginate($perPage, ['*'], 'page', $currentPage);

        $totalPages = ceil($totalRecords / $perPage);

        return response()->json([
            'products' => new ProductCollection($products),
            'totalRecords' => $totalRecords,
            'totalPages' => $totalPages,
        ]);
    }


    public function getMedicines(): JsonResponse
    {
        return response()->json(
            new ProductCollection($this->product
                    ->where('company_id', intval(session('company')))
                    ->orderBy('name', 'asc')
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
    public function store(ProductRequest $request): JsonResponse
    {
        $company = intval(session('company'));
        $request->merge(['date_boarding' => Carbon::parse($request->date_boarding)->toDateString()]);
        $request->merge(['company_id' => $company]);

        $product = $this->product->create($request->all());
        return response()->json(new ProductResource($product), 201);
    }

    /**
     * Display the specified resource.
     *
     * @param Product $product
     * @return JsonResponse
     */
        public function show(Product $product)
        {
            $product->load('company');
            return new ProductResource($product);
        }

    /**
     * Update the specified resource in storage.
     *
     * @param ProductRequest $request
     * @param Product $product
     * @return JsonResponse
     */
    public function update(ProductRequest $request, Product $product): JsonResponse
    {
        $product->update($request->all());

        // Buscar la nueva metadata (si fue enviada)
        $metadata = ProductMetadata::where('code', $request->input('product_metadata_code'))->first();

        if ($metadata) {
            // Verificar si ya existe un registro de relación (independiente del company_id)
            $relation = CompanyProductMetadata::where('product_id', $product->id)->first();

            if ($relation) {
                // Actualizar metadata sin tocar el company_id
                $relation->update([
                    'product_metadata_id' => $metadata->id
                ]);
            } else {
                // Crear nueva relación manteniendo el company_id que tenga el producto
                CompanyProductMetadata::create([
                    'company_id' => $product->company_id, // se usa el que ya está en la BD
                    'product_id' => $product->id,
                    'product_metadata_id' => $metadata->id
                ]);
            }
        }

        return response()->json(new ProductResource($product));
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

    public function update_price(ProductUpdateRequest $request, $id){
        Product::where('id', $id)->update([
            'price' => $request->price,
            'name'  => $request->name
        ]);
        return response()->json("Se actualizo correctamente");
    }
}
