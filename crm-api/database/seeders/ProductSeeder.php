<?php

namespace Database\Seeders;

use App\Models\CRM\Company;
use App\Models\CRM\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $demoCompany = Company::where('name', 'Demo Company')->first();

        $products = [
            ['name' => 'Basic Plan', 'price' => 29.90],
            ['name' => 'Professional Plan', 'price' => 79.90],
            ['name' => 'Enterprise Plan', 'price' => 199.90],
            ['name' => 'Consulting Service', 'price' => 150.00],
            ['name' => 'Training Package', 'price' => 500.00],
            ['name' => 'Setup Fee', 'price' => 99.00],
            ['name' => 'Custom Integration', 'price' => 999.00],
            ['name' => 'Support Package (Monthly)', 'price' => 49.90],
        ];

        foreach ($products as $product) {
            Product::create([
                'company_id' => $demoCompany->id,
                'name' => $product['name'],
                'price' => $product['price'],
                'active' => true,
            ]);
        }

        $this->command->info('Products created successfully!');
    }
}
