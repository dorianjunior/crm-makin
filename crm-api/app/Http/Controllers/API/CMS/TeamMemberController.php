<?php

namespace App\Http\Controllers\API\CMS;

use App\Http\Controllers\Controller;
use App\Http\Requests\CMS\StoreTeamMemberRequest;
use App\Http\Requests\CMS\UpdateTeamMemberRequest;
use App\Http\Resources\CMS\TeamMemberResource;
use App\Models\CMS\TeamMember;
use App\Services\CMS\PublishingService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class TeamMemberController extends Controller
{
    public function __construct(
        private readonly PublishingService $publishingService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $query = TeamMember::with(['site', 'creator']);

        if ($request->has('site_id')) {
            $query->forSite($request->site_id);
        }

        if ($request->has('department')) {
            $query->byDepartment($request->department);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $teamMembers = $query->orderBy('order')->paginate(50);

        return TeamMemberResource::collection($teamMembers);
    }

    public function store(StoreTeamMemberRequest $request): TeamMemberResource
    {
        $teamMember = TeamMember::create($request->validated());

        return new TeamMemberResource($teamMember->load(['site', 'creator']));
    }

    public function show(TeamMember $teamMember): TeamMemberResource
    {
        $teamMember->load(['site', 'creator', 'versions', 'approvals']);

        return new TeamMemberResource($teamMember);
    }

    public function update(UpdateTeamMemberRequest $request, TeamMember $teamMember): TeamMemberResource
    {
        $teamMember->update($request->validated());

        return new TeamMemberResource($teamMember->load(['site', 'creator']));
    }

    public function destroy(TeamMember $teamMember): Response
    {
        $teamMember->delete();

        return response()->noContent();
    }

    public function publish(TeamMember $teamMember): TeamMemberResource
    {
        $this->publishingService->publish($teamMember);

        return new TeamMemberResource($teamMember);
    }

    public function unpublish(TeamMember $teamMember): TeamMemberResource
    {
        $this->publishingService->unpublish($teamMember);

        return new TeamMemberResource($teamMember);
    }

    public function requestApproval(TeamMember $teamMember): TeamMemberResource
    {
        $this->publishingService->requestApproval($teamMember);

        return new TeamMemberResource($teamMember);
    }
}
