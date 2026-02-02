<?php

namespace App\Http\Controllers\API\CMS;

use App\Http\Controllers\Controller;
use App\Http\Resources\CMS\ContentApprovalResource;
use App\Models\CMS\ContentApproval;
use App\Services\CMS\PublishingService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ContentApprovalController extends Controller
{
    public function __construct(
        private readonly PublishingService $publishingService
    ) {}

    /**
     * List all pending approvals
     */
    public function index(Request $request)
    {
        $query = ContentApproval::with(['approvable', 'requestedBy', 'reviewedBy'])
            ->latest();

        if ($request->has('status')) {
            $query->where('status', $request->status);
        } else {
            $query->pending();
        }

        if ($request->has('approvable_type')) {
            $query->where('approvable_type', $request->approvable_type);
        }

        $approvals = $query->paginate(20);

        return ContentApprovalResource::collection($approvals);
    }

    /**
     * Show a specific approval
     */
    public function show(ContentApproval $approval)
    {
        $approval->load(['approvable', 'requestedBy', 'reviewedBy']);

        return new ContentApprovalResource($approval);
    }

    /**
     * Approve a content approval request
     */
    public function approve(Request $request, ContentApproval $approval)
    {
        if ($approval->status !== 'pending') {
            return response()->json([
                'message' => 'Esta solicitação já foi processada.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $this->publishingService->approve($approval, auth()->id());

        return response()->json([
            'message' => 'Conteúdo aprovado e publicado com sucesso.',
            'approval' => new ContentApprovalResource($approval->fresh(['approvable', 'requestedBy', 'reviewedBy'])),
        ]);
    }

    /**
     * Reject a content approval request
     */
    public function reject(Request $request, ContentApproval $approval)
    {
        $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        if ($approval->status !== 'pending') {
            return response()->json([
                'message' => 'Esta solicitação já foi processada.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $this->publishingService->reject($approval, auth()->id(), $request->reason);

        return response()->json([
            'message' => 'Solicitação rejeitada com sucesso.',
            'approval' => new ContentApprovalResource($approval->fresh(['approvable', 'requestedBy', 'reviewedBy'])),
        ]);
    }

    /**
     * Get statistics about approvals
     */
    public function statistics()
    {
        return response()->json([
            'pending' => ContentApproval::pending()->count(),
            'approved' => ContentApproval::where('status', 'approved')->count(),
            'rejected' => ContentApproval::where('status', 'rejected')->count(),
            'total' => ContentApproval::count(),
            'by_type' => ContentApproval::selectRaw('approvable_type, COUNT(*) as count')
                ->groupBy('approvable_type')
                ->get()
                ->mapWithKeys(function ($item) {
                    $type = class_basename($item->approvable_type);

                    return [$type => $item->count];
                }),
        ]);
    }
}
