<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['company', 'role']);

        if ($request->has('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        $users = $query->paginate(15);

        return response()->json($users);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'role_id' => 'required|exists:roles,id',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'active' => 'boolean',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $user = User::create($validated);

        return response()->json($user->load(['company', 'role']), 201);
    }

    public function show(User $user)
    {
        return response()->json($user->load(['company', 'role', 'assignedLeads', 'tasks']));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'company_id' => 'sometimes|exists:companies,id',
            'role_id' => 'sometimes|exists:roles,id',
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,'.$user->id,
            'password' => 'sometimes|string|min:8',
            'active' => 'boolean',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        return response()->json($user->load(['company', 'role']));
    }

    public function destroy(User $user)
    {
        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }
}
