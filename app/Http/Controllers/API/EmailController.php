<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\CRM\Email;
use Illuminate\Http\Request;

class EmailController extends Controller
{
    public function index(Request $request)
    {
        $query = Email::with(['lead', 'user']);

        if ($request->has('lead_id')) {
            $query->where('lead_id', $request->lead_id);
        }

        $emails = $query->orderBy('sent_at', 'desc')->paginate(15);

        return response()->json($emails);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'lead_id' => 'required|exists:leads,id',
            'user_id' => 'required|exists:users,id',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'sent_at' => 'nullable|date',
        ]);

        $email = Email::create($validated);

        return response()->json($email->load(['lead', 'user']), 201);
    }

    public function show(Email $email)
    {
        return response()->json($email->load(['lead', 'user']));
    }

    public function update(Request $request, Email $email)
    {
        $validated = $request->validate([
            'subject' => 'sometimes|string|max:255',
            'body' => 'sometimes|string',
            'sent_at' => 'nullable|date',
        ]);

        $email->update($validated);

        return response()->json($email->load(['lead', 'user']));
    }

    public function destroy(Email $email)
    {
        $email->delete();

        return response()->json(['message' => 'Email deleted successfully']);
    }
}
