<?php

namespace App\Http\Controllers\API\CRM;

use App\Http\Controllers\Controller;
use App\Http\Requests\CRM\StoreProposalRequest;
use App\Http\Requests\CRM\UpdateProposalRequest;
use App\Http\Resources\CRM\ProposalResource;
use App\Models\CRM\Proposal;
use App\Services\CRM\ProposalService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ProposalController extends Controller
{
    public function __construct(
        private readonly ProposalService $proposalService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Proposal::with(['lead', 'items.product']);

        if ($request->has('lead_id')) {
            $query->where('lead_id', $request->lead_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $proposals = $query->paginate(15);

        return ProposalResource::collection($proposals);
    }

    public function store(StoreProposalRequest $request): ProposalResource
    {
        $proposal = $this->proposalService->create(
            $request->validated(),
            $request->input('items', [])
        );

        return new ProposalResource($proposal->load(['lead', 'items.product']));
    }

    public function show(Proposal $proposal): ProposalResource
    {
        $proposal->load(['lead', 'items.product']);

        return new ProposalResource($proposal);
    }

    public function update(UpdateProposalRequest $request, Proposal $proposal): ProposalResource
    {
        $proposal = $this->proposalService->update($proposal, $request->validated());

        return new ProposalResource($proposal->load(['lead', 'items.product']));
    }

    public function destroy(Proposal $proposal): Response
    {
        $this->proposalService->delete($proposal);

        return response()->noContent();
    }

    public function accept(Proposal $proposal): ProposalResource
    {
        $proposal = $this->proposalService->markAsAccepted($proposal);

        return new ProposalResource($proposal);
    }

    public function reject(Proposal $proposal): ProposalResource
    {
        $proposal = $this->proposalService->markAsRejected($proposal);

        return new ProposalResource($proposal);
    }

    public function addItem(Request $request, Proposal $proposal): ProposalResource
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
        ]);

        $proposal = $this->proposalService->addItem(
            $proposal,
            $request->input('product_id'),
            $request->input('quantity'),
            $request->input('price')
        );

        return new ProposalResource($proposal->load(['items.product']));
    }

    public function removeItem(Proposal $proposal, int $itemId): ProposalResource
    {
        $proposal = $this->proposalService->removeItem($proposal, $itemId);

        return new ProposalResource($proposal->load(['items.product']));
    }
}
