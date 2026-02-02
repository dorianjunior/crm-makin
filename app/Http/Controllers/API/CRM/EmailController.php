<?php

namespace App\Http\Controllers\API\CRM;

use App\Http\Controllers\Controller;
use App\Http\Requests\CRM\StoreEmailRequest;
use App\Http\Requests\CRM\UpdateEmailRequest;
use App\Http\Resources\CRM\EmailResource;
use App\Models\CRM\Email;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class EmailController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Email::with('lead');

        if ($request->has('lead_id')) {
            $query->where('lead_id', $request->lead_id);
        }

        $emails = $query->orderBy('sent_at', 'desc')->paginate(15);

        return EmailResource::collection($emails);
    }

    public function store(StoreEmailRequest $request): EmailResource
    {
        $email = Email::create(array_merge(
            $request->validated(),
            ['sent_at' => now()]
        ));

        return new EmailResource($email->load('lead'));
    }

    public function show(Email $email): EmailResource
    {
        $email->load('lead');

        return new EmailResource($email);
    }

    public function update(UpdateEmailRequest $request, Email $email): EmailResource
    {
        $email->update($request->validated());

        return new EmailResource($email->load('lead'));
    }

    public function destroy(Email $email): Response
    {
        $email->delete();

        return response()->noContent();
    }
}
