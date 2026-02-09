<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\CRM\StoreProductRequest;
use App\Http\Requests\CRM\UpdateProductRequest;
use App\Models\CRM\Product;
use App\Services\CRM\ProductService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function __construct(private readonly ProductService $productService) {}

    public function index(Request $request): Response
    {
        $companyId = auth()->user()->company_id;
        $filters = $request->only(['search', 'type', 'active', 'page']);

        $products = $this->productService->getForIndex($companyId, $filters, 15);
        $stats = $this->productService->getStats($companyId);

        return Inertia::render('CRM/Products/Index', [
            'products' => $products,
            'stats' => $stats,
            'filters' => $filters,
        ]);
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['company_id'] = auth()->user()->company_id;

        $this->productService->create($data);

        return redirect()->back()->with('success', 'Produto criado com sucesso!');
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        if ($product->company_id !== auth()->user()->company_id) {
            abort(403);
        }

        $this->productService->update($product, $request->validated());

        return redirect()->back()->with('success', 'Produto atualizado com sucesso!');
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($product->company_id !== auth()->user()->company_id) {
            abort(403);
        }

        $this->productService->delete($product);

        return redirect()->back()->with('success', 'Produto excluído com sucesso!');
    }

    public function duplicate(Product $product): RedirectResponse
    {
        if ($product->company_id !== auth()->user()->company_id) {
            abort(403);
        }

        $data = $product->toArray();
        unset($data['id'], $data['created_at'], $data['updated_at']);
        $data['name'] = $data['name'].' (Cópia)';

        $this->productService->create($data);

        return redirect()->back()->with('success', 'Produto duplicado com sucesso!');
    }
}
