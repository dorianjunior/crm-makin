<?php

namespace App\Http\Controllers\API\CRM;

use App\Http\Controllers\Controller;
use App\Http\Requests\CRM\StoreProductRequest;
use App\Http\Requests\CRM\UpdateProductRequest;
use App\Http\Resources\CRM\ProductResource;
use App\Models\CRM\Product;
use App\Services\CRM\ProductService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductService $productService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->only(['company_id', 'active', 'search']);
        $products = $this->productService->search($filters);

        return ProductResource::collection($products);
    }

    public function store(StoreProductRequest $request): ProductResource
    {
        $product = $this->productService->create($request->validated());

        return new ProductResource($product);
    }

    public function show(Product $product): ProductResource
    {
        return new ProductResource($product);
    }

    public function update(UpdateProductRequest $request, Product $product): ProductResource
    {
        $product = $this->productService->update($product, $request->validated());

        return new ProductResource($product);
    }

    public function destroy(Product $product): Response
    {
        $this->productService->delete($product);

        return response()->noContent();
    }

    public function activate(Product $product): ProductResource
    {
        $product = $this->productService->activate($product);

        return new ProductResource($product);
    }

    public function deactivate(Product $product): ProductResource
    {
        $product = $this->productService->deactivate($product);

        return new ProductResource($product);
    }
}
