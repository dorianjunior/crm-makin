<?php

namespace App\Http\Controllers;

use App\Models\AIPromptTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AIPromptTemplateController extends Controller
{
    /**
     * Get all prompt templates for a company.
     */
    public function index(Request $request): JsonResponse
    {
        $companyId = $request->user()->company_id;

        $query = AIPromptTemplate::where('company_id', $companyId);

        // Filter by category
        if ($request->has('category')) {
            $query->category($request->category);
        }

        // Filter by tag
        if ($request->has('tag')) {
            $query->withTag($request->tag);
        }

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $templates = $query->orderBy('usage_count', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => $templates,
        ]);
    }

    /**
     * Get a specific prompt template.
     */
    public function show(int $id): JsonResponse
    {
        $template = AIPromptTemplate::findOrFail($id);

        // Verify permission
        $this->authorize('view', $template);

        return response()->json([
            'data' => $template,
        ]);
    }

    /**
     * Create a new prompt template.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'description' => 'nullable|string',
            'system_prompt' => 'required|string',
            'user_prompt_template' => 'required|string',
            'variables' => 'nullable|array',
            'tags' => 'nullable|array',
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

        $template = AIPromptTemplate::create($data);

        return response()->json([
            'message' => 'Prompt template created successfully',
            'data' => $template,
        ], 201);
    }

    /**
     * Update a prompt template.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $template = AIPromptTemplate::findOrFail($id);

        // Verify permission
        $this->authorize('update', $template);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'category' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'system_prompt' => 'sometimes|string',
            'user_prompt_template' => 'sometimes|string',
            'variables' => 'nullable|array',
            'tags' => 'nullable|array',
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
            'message' => 'Prompt template updated successfully',
            'data' => $template->fresh(),
        ]);
    }

    /**
     * Delete a prompt template.
     */
    public function destroy(int $id): JsonResponse
    {
        $template = AIPromptTemplate::findOrFail($id);

        // Verify permission
        $this->authorize('delete', $template);

        // Check if template is in use
        if ($template->conversations()->exists()) {
            return response()->json([
                'message' => 'Cannot delete template that is in use by conversations',
            ], 400);
        }

        $template->delete();

        return response()->json([
            'message' => 'Prompt template deleted successfully',
        ]);
    }

    /**
     * Preview a prompt template with variables.
     */
    public function preview(Request $request, int $id): JsonResponse
    {
        $template = AIPromptTemplate::findOrFail($id);

        // Verify permission
        $this->authorize('view', $template);

        $validator = Validator::make($request->all(), [
            'variables' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $variables = $request->variables;

        // Check if all required variables are provided
        if (! $template->hasAllRequiredVariables($variables)) {
            return response()->json([
                'message' => 'Missing required variables',
                'required' => $template->getRequiredVariables(),
                'provided' => array_keys($variables),
            ], 400);
        }

        $renderedPrompt = $template->renderUserPrompt($variables);

        return response()->json([
            'data' => [
                'system_prompt' => $template->system_prompt,
                'user_prompt' => $renderedPrompt,
            ],
        ]);
    }

    /**
     * Get template statistics.
     */
    public function statistics(int $id): JsonResponse
    {
        $template = AIPromptTemplate::findOrFail($id);

        // Verify permission
        $this->authorize('view', $template);

        $stats = [
            'usage_count' => $template->usage_count,
            'avg_satisfaction_rating' => $template->avg_satisfaction_rating,
            'total_conversations' => $template->conversations()->count(),
            'active_conversations' => $template->conversations()->active()->count(),
            'completed_conversations' => $template->conversations()->completed()->count(),
        ];

        return response()->json([
            'data' => $stats,
        ]);
    }
}
