<?php

namespace App\Http\Controllers\API\Reports;

use App\Http\Controllers\Controller;
use App\Models\Reports\Report;
use App\Models\Reports\ReportSchedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportScheduleController extends Controller
{
    /**
     * List all schedules for a report
     */
    public function index(int $reportId): JsonResponse
    {
        $report = Report::query()
            ->forCompany(auth()->user()->company_id)
            ->forUser(auth()->id())
            ->findOrFail($reportId);

        $schedules = $report->schedules()->latest()->get();

        return response()->json($schedules);
    }

    /**
     * Show a specific schedule
     */
    public function show(int $reportId, int $id): JsonResponse
    {
        $report = Report::query()
            ->forCompany(auth()->user()->company_id)
            ->forUser(auth()->id())
            ->findOrFail($reportId);

        $schedule = $report->schedules()->findOrFail($id);

        return response()->json($schedule);
    }

    /**
     * Create a new schedule
     */
    public function store(Request $request, int $reportId): JsonResponse
    {
        $report = Report::query()
            ->forCompany(auth()->user()->company_id)
            ->forUser(auth()->id())
            ->findOrFail($reportId);

        $validated = $request->validate([
            'frequency' => 'required|in:daily,weekly,monthly,quarterly,yearly',
            'schedule_config' => 'nullable|array',
            'recipients' => 'nullable|array',
            'recipients.*' => 'email',
            'format' => 'required|in:pdf,excel,csv',
            'is_active' => 'boolean',
        ]);

        $schedule = $report->schedules()->create($validated);

        return response()->json([
            'message' => 'Agendamento criado com sucesso',
            'schedule' => $schedule,
        ], 201);
    }

    /**
     * Update a schedule
     */
    public function update(Request $request, int $reportId, int $id): JsonResponse
    {
        $report = Report::query()
            ->forCompany(auth()->user()->company_id)
            ->forUser(auth()->id())
            ->findOrFail($reportId);

        $schedule = $report->schedules()->findOrFail($id);

        $validated = $request->validate([
            'frequency' => 'in:daily,weekly,monthly,quarterly,yearly',
            'schedule_config' => 'nullable|array',
            'recipients' => 'nullable|array',
            'recipients.*' => 'email',
            'format' => 'in:pdf,excel,csv',
            'is_active' => 'boolean',
        ]);

        $schedule->update($validated);

        if (isset($validated['frequency']) || isset($validated['schedule_config'])) {
            $schedule->calculateNextRun();
            $schedule->save();
        }

        return response()->json([
            'message' => 'Agendamento atualizado com sucesso',
            'schedule' => $schedule->fresh(),
        ]);
    }

    /**
     * Delete a schedule
     */
    public function destroy(int $reportId, int $id): JsonResponse
    {
        $report = Report::query()
            ->forCompany(auth()->user()->company_id)
            ->forUser(auth()->id())
            ->findOrFail($reportId);

        $schedule = $report->schedules()->findOrFail($id);
        $schedule->delete();

        return response()->json([
            'message' => 'Agendamento deletado com sucesso',
        ]);
    }

    /**
     * Activate a schedule
     */
    public function activate(int $reportId, int $id): JsonResponse
    {
        $report = Report::query()
            ->forCompany(auth()->user()->company_id)
            ->forUser(auth()->id())
            ->findOrFail($reportId);

        $schedule = $report->schedules()->findOrFail($id);
        $schedule->activate();

        return response()->json([
            'message' => 'Agendamento ativado com sucesso',
            'schedule' => $schedule,
        ]);
    }

    /**
     * Deactivate a schedule
     */
    public function deactivate(int $reportId, int $id): JsonResponse
    {
        $report = Report::query()
            ->forCompany(auth()->user()->company_id)
            ->forUser(auth()->id())
            ->findOrFail($reportId);

        $schedule = $report->schedules()->findOrFail($id);
        $schedule->deactivate();

        return response()->json([
            'message' => 'Agendamento desativado com sucesso',
            'schedule' => $schedule,
        ]);
    }

    /**
     * Add recipient to schedule
     */
    public function addRecipient(Request $request, int $reportId, int $id): JsonResponse
    {
        $report = Report::query()
            ->forCompany(auth()->user()->company_id)
            ->forUser(auth()->id())
            ->findOrFail($reportId);

        $schedule = $report->schedules()->findOrFail($id);

        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        $schedule->addRecipient($validated['email']);

        return response()->json([
            'message' => 'Destinatário adicionado com sucesso',
            'recipients' => $schedule->getRecipientsList(),
        ]);
    }

    /**
     * Remove recipient from schedule
     */
    public function removeRecipient(Request $request, int $reportId, int $id): JsonResponse
    {
        $report = Report::query()
            ->forCompany(auth()->user()->company_id)
            ->forUser(auth()->id())
            ->findOrFail($reportId);

        $schedule = $report->schedules()->findOrFail($id);

        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        $schedule->removeRecipient($validated['email']);

        return response()->json([
            'message' => 'Destinatário removido com sucesso',
            'recipients' => $schedule->getRecipientsList(),
        ]);
    }

    /**
     * Get available frequencies
     */
    public function frequencies(): JsonResponse
    {
        return response()->json([
            'frequencies' => ReportSchedule::getAvailableFrequencies(),
        ]);
    }

    /**
     * Get available formats
     */
    public function formats(): JsonResponse
    {
        return response()->json([
            'formats' => ReportSchedule::getAvailableFormats(),
        ]);
    }
}
