<?php

namespace App\Http\Controllers\API\CRM;

use App\Http\Controllers\Controller;
use App\Http\Requests\CRM\StoreFileRequest;
use App\Http\Resources\CRM\FileResource;
use App\Models\CRM\File;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = File::with('lead');

        if ($request->has('lead_id')) {
            $query->where('lead_id', $request->lead_id);
        }

        $files = $query->orderBy('created_at', 'desc')->paginate(15);

        return FileResource::collection($files);
    }

    public function store(StoreFileRequest $request): FileResource
    {
        $uploadedFile = $request->file('file');

        $path = $uploadedFile->store('files', 'public');

        $file = File::create([
            'lead_id' => $request->input('lead_id'),
            'filename' => $uploadedFile->getClientOriginalName(),
            'path' => $path,
            'size' => $uploadedFile->getSize(),
            'mime_type' => $uploadedFile->getMimeType(),
        ]);

        return new FileResource($file->load('lead'));
    }

    public function show(File $file): FileResource
    {
        $file->load('lead');

        return new FileResource($file);
    }

    public function destroy(File $file): Response
    {
        Storage::disk('public')->delete($file->path);
        $file->delete();

        return response()->noContent();
    }

    public function download(File $file)
    {
        return Storage::disk('public')->download($file->path, $file->filename);
    }
}
