<?php

namespace App\Http\Controllers\API\CRM;

use App\Http\Controllers\Controller;
use App\Http\Requests\CRM\StoreWhatsappMessageRequest;
use App\Http\Requests\CRM\UpdateWhatsappMessageRequest;
use App\Http\Resources\CRM\WhatsappMessageResource;
use App\Models\CRM\WhatsappMessage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class WhatsappMessageController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = WhatsappMessage::with('lead');

        if ($request->has('lead_id')) {
            $query->where('lead_id', $request->lead_id);
        }

        $messages = $query->orderBy('sent_at', 'desc')->paginate(15);

        return WhatsappMessageResource::collection($messages);
    }

    public function store(StoreWhatsappMessageRequest $request): WhatsappMessageResource
    {
        $message = WhatsappMessage::create(array_merge(
            $request->validated(),
            ['sent_at' => now()]
        ));

        return new WhatsappMessageResource($message->load('lead'));
    }

    public function show(WhatsappMessage $whatsappMessage): WhatsappMessageResource
    {
        $whatsappMessage->load('lead');

        return new WhatsappMessageResource($whatsappMessage);
    }

    public function update(UpdateWhatsappMessageRequest $request, WhatsappMessage $whatsappMessage): WhatsappMessageResource
    {
        $whatsappMessage->update($request->validated());

        return new WhatsappMessageResource($whatsappMessage->load('lead'));
    }

    public function destroy(WhatsappMessage $whatsappMessage): Response
    {
        $whatsappMessage->delete();

        return response()->noContent();
    }
}
