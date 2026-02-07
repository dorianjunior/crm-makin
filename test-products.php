<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing Products Query...\n\n";

try {
    $user = App\Models\User::first();
    if (!$user) {
        echo "No user found!\n";
        exit(1);
    }

    echo "User: {$user->name} (Company ID: {$user->company_id})\n\n";

    $products = App\Models\CRM\Product::where('company_id', $user->company_id)
        ->with('company')
        ->paginate(15);

    echo "Products found: " . $products->total() . "\n";
    echo "Current page: " . $products->currentPage() . "\n";
    echo "Per page: " . $products->perPage() . "\n\n";

    foreach ($products as $product) {
        echo "- {$product->name} ({$product->sku}) - R$ {$product->price}\n";
    }

    echo "\n\nStats:\n";
    $service = new App\Services\CRM\ProductService();
    $stats = $service->getStats($user->company_id);
    print_r($stats);

    echo "\n✅ Test completed successfully!\n";

} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " (line " . $e->getLine() . ")\n";
    exit(1);
}
