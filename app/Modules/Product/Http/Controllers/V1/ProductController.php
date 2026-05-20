<?php

namespace App\Modules\Product\Http\Controllers\V1;

use App\Core\Http\Controllers\ApiController;
use App\Modules\Product\DTOs\ProductDTO;
use App\Modules\Product\Http\Requests\StoreProductRequest;
use App\Modules\Product\Http\Requests\UpdateProductRequest;
use App\Modules\Product\Http\Resources\ProductResource;
use App\Modules\Product\Models\Product;
use App\Modules\Product\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends ApiController
{
    public function __construct(private ProductService $service)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Product::class);

        $products = $this->service->paginate((int) $request->get('per_page', 15));

        return $this->success(
            ProductResource::collection($products),
            meta: [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ]
        );
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = $this->service->store(ProductDTO::fromArray($request->validated()));

        return $this->created(new ProductResource($product));
    }

    public function show(Product $product): JsonResponse
    {
        $this->authorize('view', $product);

        return $this->success(new ProductResource($product));
    }

    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        $updated = $this->service->update($product->id, $request->validated());

        return $this->success(new ProductResource($updated));
    }

    public function destroy(Product $product): JsonResponse
    {
        $this->authorize('delete', $product);
        $this->service->delete($product->id);

        return $this->success(message: 'Produto removido.');
    }
}
