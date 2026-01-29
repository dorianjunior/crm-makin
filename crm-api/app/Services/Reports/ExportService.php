<?php

namespace App\Services\Reports;

use App\Models\Report;
use App\Models\ReportExport;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ExportService
{
    private ReportService $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Export a report to specified format
     */
    public function export(Report $report, string $format, int $userId, array $filters = []): ReportExport
    {
        $export = ReportExport::create([
            'report_id' => $report->id,
            'user_id' => $userId,
            'format' => $format,
            'filename' => $this->generateFilename($report, $format),
            'file_path' => '', // Will be set after generation
            'status' => 'pending',
            'filters_used' => $filters,
        ]);

        try {
            $export->markAsProcessing();

            // Execute report
            $results = $this->reportService->execute($report, $filters);

            // Generate file based on format
            $filePath = match($format) {
                'pdf' => $this->generatePdf($report, $results, $export->filename),
                'excel' => $this->generateExcel($report, $results, $export->filename),
                'csv' => $this->generateCsv($report, $results, $export->filename),
                default => throw new \Exception("Format '{$format}' not supported"),
            };

            $export->file_path = $filePath;
            $export->markAsCompleted(
                rowsCount: count($results['data']),
                fileSize: Storage::size($filePath)
            );

        } catch (\Exception $e) {
            $export->markAsFailed($e->getMessage());
            throw $e;
        }

        return $export;
    }

    /**
     * Generate PDF file
     */
    private function generatePdf(Report $report, array $results, string $filename): string
    {
        // Note: Requires dompdf or similar library
        // For now, this is a stub that creates a simple HTML file
        
        $html = $this->generateHtmlReport($report, $results);
        
        $path = 'exports/pdf/' . $filename;
        Storage::put($path, $html);
        
        // TODO: Convert HTML to PDF using library like dompdf
        // $pdf = Pdf::loadHTML($html);
        // Storage::put($path, $pdf->output());
        
        return $path;
    }

    /**
     * Generate Excel file
     */
    private function generateExcel(Report $report, array $results, string $filename): string
    {
        // Note: Requires PhpSpreadsheet or Laravel Excel
        // For now, this creates a CSV-like format
        
        $data = $results['data'];
        $columns = $report->columns ?? array_keys($data[0] ?? []);
        
        $content = [];
        
        // Header
        $content[] = implode(',', $columns);
        
        // Data rows
        foreach ($data as $row) {
            $values = [];
            foreach ($columns as $column) {
                $value = is_array($row) ? ($row[$column] ?? '') : ($row->$column ?? '');
                $values[] = $this->escapeCsvValue($value);
            }
            $content[] = implode(',', $values);
        }
        
        $path = 'exports/excel/' . str_replace('.xlsx', '.csv', $filename);
        Storage::put($path, implode("\n", $content));
        
        // TODO: Convert to real Excel using PhpSpreadsheet
        
        return $path;
    }

    /**
     * Generate CSV file
     */
    private function generateCsv(Report $report, array $results, string $filename): string
    {
        $data = $results['data'];
        $columns = $report->columns ?? array_keys($data[0] ?? []);
        
        $content = [];
        
        // Header
        $content[] = implode(',', $columns);
        
        // Data rows
        foreach ($data as $row) {
            $values = [];
            foreach ($columns as $column) {
                $value = is_array($row) ? ($row[$column] ?? '') : ($row->$column ?? '');
                $values[] = $this->escapeCsvValue($value);
            }
            $content[] = implode(',', $values);
        }
        
        $path = 'exports/csv/' . $filename;
        Storage::put($path, implode("\n", $content));
        
        return $path;
    }

    /**
     * Generate HTML report (for PDF conversion)
     */
    private function generateHtmlReport(Report $report, array $results): string
    {
        $data = $results['data'];
        $summary = $results['summary'] ?? [];
        $columns = $report->columns ?? array_keys($data[0] ?? []);
        
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>' . htmlspecialchars($report->name) . '</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
        .meta { color: #666; margin-bottom: 20px; }
        .summary { background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .summary-item { display: inline-block; margin-right: 20px; }
        .summary-label { font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background: #007bff; color: white; padding: 10px; text-align: left; }
        td { padding: 8px; border-bottom: 1px solid #ddd; }
        tr:nth-child(even) { background: #f8f9fa; }
        .footer { margin-top: 30px; text-align: center; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <h1>' . htmlspecialchars($report->name) . '</h1>
    <div class="meta">
        <p><strong>Tipo:</strong> ' . htmlspecialchars($report->type) . '</p>
        <p><strong>Gerado em:</strong> ' . now()->format('d/m/Y H:i:s') . '</p>
        <p><strong>Total de registros:</strong> ' . count($data) . '</p>
    </div>';

        if (!empty($summary)) {
            $html .= '<div class="summary">';
            $html .= '<h3>Resumo</h3>';
            foreach ($summary as $key => $value) {
                $label = ucfirst(str_replace('_', ' ', $key));
                $html .= '<div class="summary-item"><span class="summary-label">' . $label . ':</span> ' . $value . '</div>';
            }
            $html .= '</div>';
        }

        $html .= '<table>';
        $html .= '<thead><tr>';
        foreach ($columns as $column) {
            $html .= '<th>' . htmlspecialchars(ucfirst(str_replace('_', ' ', $column))) . '</th>';
        }
        $html .= '</tr></thead>';
        
        $html .= '<tbody>';
        foreach ($data as $row) {
            $html .= '<tr>';
            foreach ($columns as $column) {
                $value = is_array($row) ? ($row[$column] ?? '') : ($row->$column ?? '');
                $html .= '<td>' . htmlspecialchars($value) . '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody>';
        
        $html .= '</table>';
        $html .= '<div class="footer">Relatório gerado automaticamente pelo CRM Makin</div>';
        $html .= '</body></html>';
        
        return $html;
    }

    /**
     * Escape CSV value
     */
    private function escapeCsvValue($value): string
    {
        if (is_array($value) || is_object($value)) {
            $value = json_encode($value);
        }
        
        $value = (string) $value;
        
        // Escape double quotes
        $value = str_replace('"', '""', $value);
        
        // Wrap in quotes if contains comma, newline, or double quote
        if (strpos($value, ',') !== false || strpos($value, "\n") !== false || strpos($value, '"') !== false) {
            $value = '"' . $value . '"';
        }
        
        return $value;
    }

    /**
     * Generate filename
     */
    private function generateFilename(Report $report, string $format): string
    {
        $slug = Str::slug($report->name);
        $timestamp = now()->format('Y-m-d_H-i-s');
        $extension = $format === 'excel' ? 'xlsx' : $format;
        
        return "{$slug}_{$timestamp}.{$extension}";
    }

    /**
     * Clean old exports
     */
    public function cleanOldExports(int $days = 7): int
    {
        $expired = ReportExport::where('expires_at', '<=', now()->subDays($days))->get();
        
        $count = 0;
        foreach ($expired as $export) {
            $export->delete(); // Will trigger file deletion in model boot
            $count++;
        }
        
        return $count;
    }

    /**
     * Get export by ID
     */
    public function getExport(int $exportId, int $userId): ?ReportExport
    {
        return ReportExport::where('id', $exportId)
            ->where('user_id', $userId)
            ->first();
    }

    /**
     * Download export
     */
    public function download(ReportExport $export): array
    {
        if (!$export->isCompleted()) {
            throw new \Exception('Export is not completed yet');
        }

        if ($export->isExpired()) {
            throw new \Exception('Export has expired');
        }

        if (!$export->fileExists()) {
            throw new \Exception('Export file not found');
        }

        return [
            'path' => Storage::path($export->file_path),
            'filename' => $export->filename,
            'mime_type' => $this->getMimeType($export->format),
        ];
    }

    /**
     * Get MIME type for format
     */
    private function getMimeType(string $format): string
    {
        return match($format) {
            'pdf' => 'application/pdf',
            'excel' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'csv' => 'text/csv',
            default => 'application/octet-stream',
        };
    }
}
