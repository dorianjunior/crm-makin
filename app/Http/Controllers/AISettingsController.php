<?php

namespace App\Http\Controllers;

use App\Models\AISetting;
use App\Models\Company;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AISettingsController extends Controller
{
    /**
     * Get all AI settings for a company.
     */
    public function index(Request $request): JsonResponse
    {
        $companyId = $request->user()->company_id;

        $settings = AISetting::where('company_id', $companyId)
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => $settings,
        ]);
    }

    /**
     * Get a specific AI setting.
     */
    public function show(int $id): JsonResponse
    {
        $setting = AISetting::findOrFail($id);

        // Verify permission
        $this->authorize('view', $setting);

        return response()->json([
            'data' => $setting,
        ]);
    }

    /**
     * Create a new AI setting.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'provider' => 'required|in:gemini,openai,claude',
            'model' => 'required|string|max:255',
            'api_key' => 'required|string',
            'temperature' => 'nullable|numeric|min:0|max:2',
            'max_tokens' => 'nullable|integer|min:1|max:100000',
            'top_p' => 'nullable|numeric|min:0|max:1',
            'top_k' => 'nullable|integer|min:1|max:100',
            'stop_sequences' => 'nullable|array',
            'safety_settings' => 'nullable|array',
            'custom_parameters' => 'nullable|array',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        $data['company_id'] = $request->user()->company_id;

        $setting = AISetting::create($data);

        // If marked as default, update other settings
        if ($setting->is_default) {
            $setting->setAsDefault();
        }

        return response()->json([
            'message' => 'AI setting created successfully',
            'data' => $setting,
        ], 201);
    }

    /**
     * Update an AI setting.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $setting = AISetting::findOrFail($id);

        // Verify permission
        $this->authorize('update', $setting);

        $validator = Validator::make($request->all(), [
            'provider' => 'sometimes|in:gemini,openai,claude',
            'model' => 'sometimes|string|max:255',
            'api_key' => 'sometimes|string',
            'temperature' => 'nullable|numeric|min:0|max:2',
            'max_tokens' => 'nullable|integer|min:1|max:100000',
            'top_p' => 'nullable|numeric|min:0|max:1',
            'top_k' => 'nullable|integer|min:1|max:100',
            'stop_sequences' => 'nullable|array',
            'safety_settings' => 'nullable|array',
            'custom_parameters' => 'nullable|array',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $setting->update($validator->validated());

        // If marked as default, update other settings
        if ($request->has('is_default') && $request->is_default) {
            $setting->setAsDefault();
        }

        return response()->json([
            'message' => 'AI setting updated successfully',
            'data' => $setting->fresh(),
        ]);
    }

    /**
     * Delete an AI setting.
     */
    public function destroy(int $id): JsonResponse
    {
        $setting = AISetting::findOrFail($id);

        // Verify permission
        $this->authorize('delete', $setting);

        // Prevent deleting the default setting if it's the only one
        if ($setting->is_default) {
            $otherSettings = AISetting::where('company_id', $setting->company_id)
                ->where('id', '!=', $id)
                ->exists();

            if (!$otherSettings) {
                return response()->json([
                    'message' => 'Cannot delete the only AI setting',
                ], 400);
            }
        }

        $setting->delete();

        return response()->json([
            'message' => 'AI setting deleted successfully',
        ]);
    }

    /**
     * Set an AI setting as default.
     */
    public function setDefault(int $id): JsonResponse
    {
        $setting = AISetting::findOrFail($id);

        // Verify permission
        $this->authorize('update', $setting);

        $setting->setAsDefault();

        return response()->json([
            'message' => 'AI setting set as default successfully',
            'data' => $setting->fresh(),
        ]);
    }

    /**
     * Test an AI setting connection.
     */
    public function test(int $id): JsonResponse
    {
        $setting = AISetting::findOrFail($id);

        // Verify permission
        $this->authorize('view', $setting);

        try {
            $service = new \App\Services\GeminiService($setting);
            $response = $service->generateResponse('Hello! Please respond with "OK" if you can hear me.');

            return response()->json([
                'message' => 'Connection test successful',
                'response' => $response['content'],
                'tokens' => $response['tokens'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Connection test failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
