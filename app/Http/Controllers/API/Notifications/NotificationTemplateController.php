<?php

namespace App\Http\Controllers\API\Notifications;

use App\Http\Controllers\Controller;
use App\Models\Notification\NotificationTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NotificationTemplateController extends Controller
{
    /**
     * Get all templates for the company.
     */
    public function index(Request $request): JsonResponse
    {
        $query = NotificationTemplate::where('company_id', $request->user()->company_id);

        // Filter by type
        if ($request->has('type')) {
            $query->type($request->type);
        }

        // Filter by channel
        if ($request->has('channel')) {
            $query->channel($request->channel);
        }

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $templates = $query->orderBy('name')->get();

        return response()->json(['data' => $templates]);
    }

    /**
     * Get a specific template.
     */
    public function show(int $id): JsonResponse
    {
        $template = NotificationTemplate::findOrFail($id);

        // Verify permission
        $this->authorize('view', $template);

        return response()->json(['data' => $template]);
    }

    /**
     * Create a new template.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'channel' => 'required|in:email,whatsapp,push,sms,in_app',
            'subject' => 'nullable|string|max:255',
            'body_template' => 'required|string',
            'variables' => 'nullable|array',
            'default_data' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        $data['company_id'] = $request->user()->company_id;

        $template = NotificationTemplate::create($data);

        return response()->json([
            'message' => 'Template created successfully',
            'data' => $template,
        ], 201);
    }

    /**
     * Update a template.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $template = NotificationTemplate::findOrFail($id);

        // Verify permission
        $this->authorize('update', $template);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'type' => 'sometimes|string|max:255',
            'channel' => 'sometimes|in:email,whatsapp,push,sms,in_app',
            'subject' => 'nullable|string|max:255',
            'body_template' => 'sometimes|string',
            'variables' => 'nullable|array',
            'default_data' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $template->update($validator->validated());

        return response()->json([
            'message' => 'Template updated successfully',
            'data' => $template->fresh(),
        ]);
    }

    /**
     * Delete a template.
     */
    public function destroy(int $id): JsonResponse
    {
        $template = NotificationTemplate::findOrFail($id);

        // Verify permission
        $this->authorize('delete', $template);

        $template->delete();

        return response()->json([
            'message' => 'Template deleted successfully',
        ]);
    }

    /**
     * Preview a template with data.
     */
    public function preview(Request $request, int $id): JsonResponse
    {
        $template = NotificationTemplate::findOrFail($id);

        // Verify permission
        $this->authorize('view', $template);

        $validator = Validator::make($request->all(), [
            'data' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $request->data;

        // Check required variables
        if (! $template->hasAllRequiredVariables($data)) {
            return response()->json([
                'message' => 'Missing required variables',
                'required' => $template->getRequiredVariables(),
                'provided' => array_keys($data),
            ], 400);
        }

        $preview = [
            'subject' => $template->renderSubject($data),
            'body' => $template->renderBody($data),
        ];

        return response()->json(['data' => $preview]);
    }

    /**
     * Get template variables.
     */
    public function variables(int $id): JsonResponse
    {
        $template = NotificationTemplate::findOrFail($id);

        // Verify permission
        $this->authorize('view', $template);

        return response()->json([
            'data' => [
                'required' => $template->getRequiredVariables(),
                'available' => $template->variables,
                'defaults' => $template->default_data,
            ],
        ]);
    }
}
