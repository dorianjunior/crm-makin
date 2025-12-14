<?php

namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public function index(Request $request)
    {
        $query = File::with(['company', 'lead', 'uploader']);

        if ($request->has('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        if ($request->has('lead_id')) {
            $query->where('lead_id', $request->lead_id);
        }

        $files = $query->paginate(15);
        return response()->json($files);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'lead_id' => 'nullable|exists:leads,id',
            'uploaded_by' => 'required|exists:users,id',
            'file' => 'required|file|max:10240', // 10MB max
        ]);

        $uploadedFile = $request->file('file');
        $path = $uploadedFile->store('uploads', 'public');

        $file = File::create([
            'company_id' => $validated['company_id'],
            'lead_id' => $validated['lead_id'] ?? null,
            'path' => $path,
            'type' => $uploadedFile->getMimeType(),
            'uploaded_by' => $validated['uploaded_by'],
        ]);

        return response()->json($file->load(['company', 'lead', 'uploader']), 201);
    }

    public function show(File $file)
    {
        return response()->json($file->load(['company', 'lead', 'uploader']));
    }

    public function download(File $file)
    {
        return Storage::disk('public')->download($file->path);
    }

    public function destroy(File $file)
    {
        Storage::disk('public')->delete($file->path);
        $file->delete();
        return response()->json(['message' => 'File deleted successfully']);
    }
}
