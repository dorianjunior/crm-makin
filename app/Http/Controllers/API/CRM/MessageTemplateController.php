<?php

namespace App\Http\Controllers\API\CRM;

use App\Http\Controllers\Controller;
use App\Http\Requests\CRM\StoreMessageTemplateRequest;
use App\Http\Requests\CRM\UpdateMessageTemplateRequest;
use App\Http\Resources\CRM\MessageTemplateResource;
use App\Models\CRM\MessageTemplate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class MessageTemplateController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = MessageTemplate::query();

        if ($request->has('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        $templates = $query->get();

        return MessageTemplateResource::collection($templates);
    }

    public function store(StoreMessageTemplateRequest $request): MessageTemplateResource
    {
        $template = MessageTemplate::create($request->validated());

        return new MessageTemplateResource($template);
    }

    public function show(MessageTemplate $messageTemplate): MessageTemplateResource
    {
        return new MessageTemplateResource($messageTemplate);
    }

    public function update(UpdateMessageTemplateRequest $request, MessageTemplate $messageTemplate): MessageTemplateResource
    {
        $messageTemplate->update($request->validated());

        return new MessageTemplateResource($messageTemplate);
    }

    public function destroy(MessageTemplate $messageTemplate): Response
    {
        $messageTemplate->delete();

        return response()->noContent();
    }
}
