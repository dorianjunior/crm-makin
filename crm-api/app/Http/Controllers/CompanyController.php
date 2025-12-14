<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function index()
    {
        $companies = Company::with(['users', 'leads'])->paginate(15);
        return response()->json($companies);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'domain' => 'nullable|string|max:255',
            'plan' => 'required|in:free,basic,premium,enterprise',
            'status' => 'required|in:active,inactive,suspended',
        ]);

        $company = Company::create($validated);
        return response()->json($company, 201);
    }

    public function show(Company $company)
    {
        return response()->json($company->load(['users', 'leads', 'products', 'pipelines']));
    }

    public function update(Request $request, Company $company)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'domain' => 'nullable|string|max:255',
            'plan' => 'sometimes|in:free,basic,premium,enterprise',
            'status' => 'sometimes|in:active,inactive,suspended',
        ]);

        $company->update($validated);
        return response()->json($company);
    }

    public function destroy(Company $company)
    {
        $company->delete();
        return response()->json(['message' => 'Company deleted successfully']);
    }
}
