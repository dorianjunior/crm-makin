<?php

namespace App\Http\Controllers;

use App\Models\CRM\WhatsappMessage;
use Illuminate\Http\Request;

class WhatsappMessageController extends Controller
{
    public function index(Request $request)
    {
        $query = WhatsappMessage::with(['lead', 'user']);

        if ($request->has('lead_id')) {
            $query->where('lead_id', $request->lead_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $messages = $query->orderBy('sent_at', 'desc')->paginate(15);

        return response()->json($messages);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'lead_id' => 'required|exists:leads,id',
            'user_id' => 'required|exists:users,id',
            'message' => 'required|string',
            'status' => 'required|in:queued,sent,delivered,failed',
            'sent_at' => 'nullable|date',
        ]);

        $whatsappMessage = WhatsappMessage::create($validated);

        return response()->json($whatsappMessage->load(['lead', 'user']), 201);
    }

    public function show(WhatsappMessage $whatsappMessage)
    {
        return response()->json($whatsappMessage->load(['lead', 'user']));
    }

    public function update(Request $request, WhatsappMessage $whatsappMessage)
    {
        $validated = $request->validate([
            'message' => 'sometimes|string',
            'status' => 'sometimes|in:queued,sent,delivered,failed',
            'sent_at' => 'nullable|date',
        ]);

        $whatsappMessage->update($validated);

        return response()->json($whatsappMessage->load(['lead', 'user']));
    }

    public function destroy(WhatsappMessage $whatsappMessage)
    {
        $whatsappMessage->delete();

        return response()->json(['message' => 'WhatsApp message deleted successfully']);
    }
}
