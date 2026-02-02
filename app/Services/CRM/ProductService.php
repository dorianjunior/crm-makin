<?php

namespace App\Services\CRM;

use App\Models\CRM\Product;
use Illuminate\Support\Collection;

class ProductService
{
    public function create(array $data): Product
    {
        return Product::create($data);
    }

    public function update(Product $product, array $data): Product
    {
        $product->update($data);

        return $product->fresh();
    }

    public function delete(Product $product): bool
    {
        return $product->delete();
    }

    public function getByCompany(int $companyId, bool $activeOnly = false): Collection
    {
        $query = Product::where('company_id', $companyId);

        if ($activeOnly) {
            $query->where('active', true);
        }

        return $query->get();
    }

    public function activate(Product $product): Product
    {
        $product->update(['active' => true]);

        return $product->fresh();
    }

    public function deactivate(Product $product): Product
    {
        $product->update(['active' => false]);

        return $product->fresh();
    }

    public function updatePrice(Product $product, float $newPrice): Product
    {
        $product->update(['price' => $newPrice]);

        return $product->fresh();
    }

    public function search(int $companyId, string $term): Collection
    {
        return Product::where('company_id', $companyId)
            ->where('name', 'like', "%{$term}%")
            ->get();
    }
}
