<?php

namespace App\Http\Controllers;

use App\Models\MessageTemplate;
use Illuminate\Http\Request;

class MessageTemplateController extends Controller
{
    public function index(Request $request)
    {
        $query = MessageTemplate::with('company');

        if ($request->has('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        $templates = $query->get();
        return response()->json($templates);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'type' => 'required|in:email,whatsapp,sms',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $template = MessageTemplate::create($validated);
        return response()->json($template, 201);
    }

    public function show(MessageTemplate $messageTemplate)
    {
        return response()->json($messageTemplate->load('company'));
    }

    public function update(Request $request, MessageTemplate $messageTemplate)
    {
        $validated = $request->validate([
            'type' => 'sometimes|in:email,whatsapp,sms',
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
        ]);

        $messageTemplate->update($validated);
        return response()->json($messageTemplate);
    }

    public function destroy(MessageTemplate $messageTemplate)
    {
        $messageTemplate->delete();
        return response()->json(['message' => 'Message template deleted successfully']);
    }
}
