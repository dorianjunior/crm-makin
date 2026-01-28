<?php

namespace App\Http\Controllers\API\CRM;

use App\Http\Controllers\Controller;
use App\Http\Requests\CRM\StoreCompanyRequest;
use App\Http\Requests\CRM\UpdateCompanyRequest;
use App\Http\Resources\CRM\CompanyResource;
use App\Models\CRM\Company;
use App\Services\CRM\CompanyService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class CompanyController extends Controller
{
    public function __construct(
        private readonly CompanyService $companyService
    ) {}

    public function index(): AnonymousResourceCollection
    {
        $companies = Company::with(['users', 'leads'])
            ->withCount(['users', 'leads'])
            ->paginate(15);

        return CompanyResource::collection($companies);
    }

    public function store(StoreCompanyRequest $request): CompanyResource
    {
        $company = $this->companyService->create($request->validated());

        return new CompanyResource($company);
    }

    public function show(Company $company): CompanyResource
    {
        $company->load(['users', 'leads', 'products', 'pipelines'])
            ->loadCount(['users', 'leads']);

        return new CompanyResource($company);
    }

    public function update(UpdateCompanyRequest $request, Company $company): CompanyResource
    {
        $company = $this->companyService->update($company, $request->validated());

        return new CompanyResource($company);
    }

    public function destroy(Company $company): Response
    {
        $this->companyService->delete($company);

        return response()->noContent();
    }

    public function activate(Company $company): CompanyResource
    {
        $company = $this->companyService->activate($company);

        return new CompanyResource($company);
    }

    public function deactivate(Company $company): CompanyResource
    {
        $company = $this->companyService->deactivate($company);

        return new CompanyResource($company);
    }

    public function suspend(Company $company): CompanyResource
    {
        $company = $this->companyService->suspend($company);

        return new CompanyResource($company);
    }
}
