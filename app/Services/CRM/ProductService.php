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

    /**
     * Get paginated products for index page with filters
     */
    public function getForIndex(int $companyId, array $filters = [], int $perPage = 15)
    {
        $query = Product::where('company_id', $companyId)
            ->with('company');

        if (! empty($filters['search'])) {
            $term = $filters['search'];
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('sku', 'like', "%{$term}%");
            });
        }

        if (isset($filters['type']) && $filters['type'] !== '') {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['active']) && $filters['active'] !== '') {
            $query->where('active', (bool) $filters['active']);
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Get simple stats for products overview
     */
    public function getStats(int $companyId): array
    {
        $total = Product::where('company_id', $companyId)->count();
        $active = Product::where('company_id', $companyId)->where('active', true)->count();
        $inactive = max(0, $total - $active);
        $totalValue = (float) Product::where('company_id', $companyId)->sum('price');

        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $inactive,
            'total_value' => $totalValue,
        ];
    }
}
