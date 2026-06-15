<?php

namespace App\Services;

use App\Models\Report;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use RuntimeException;

class ReportExportService
{
    public function resolveDownloadPath(Report $report, string $format): string
    {
        return match ($format) {
            'html' => $this->resolveHtmlPath($report),
            'pdf' => $this->resolvePdfPath($report),
            'excel' => $this->resolveExcelPath($report),
            default => throw new RuntimeException('Unsupported export format.'),
        };
    }

    private function resolveHtmlPath(Report $report): string
    {
        if ($report->web_html_path === null) {
            throw new RuntimeException('HTML report is not available.');
        }

        return $report->web_html_path;
    }

    private function resolvePdfPath(Report $report): string
    {
        $report->loadMissing('analysisJob');

        if ($report->pdf_path !== null && Storage::disk(config('filesystems.default'))->exists($report->pdf_path)) {
            return $report->pdf_path;
        }

        if ($report->web_html_path === null) {
            throw new RuntimeException('HTML report is required before PDF export.');
        }

        $html = Storage::disk(config('filesystems.default'))->get($report->web_html_path);
        $pdfBinary = $this->renderPdf((string) $html);
        $path = sprintf('reports/%s/report.pdf', $report->analysisJob->job_id);

        Storage::disk(config('filesystems.default'))->put($path, $pdfBinary);
        $report->update(['pdf_path' => $path]);

        return $path;
    }

    private function resolveExcelPath(Report $report): string
    {
        $report->loadMissing(['analysisJob.results', 'analysisJob.columns']);

        if ($report->excel_path !== null && Storage::disk(config('filesystems.default'))->exists($report->excel_path)) {
            return $report->excel_path;
        }

        $spreadsheet = new Spreadsheet;

        $summary = $spreadsheet->getActiveSheet();
        $summary->setTitle('Summary');
        $summary->fromArray([
            ['Field', 'Value'],
            ['Report Title', $report->title],
            ['Job ID', $report->analysisJob->job_id],
            ['Objectives', $report->analysisJob->objectives],
            ['Executive Summary', $report->executive_summary],
            ['AI Interpretation', $report->ai_interpretation],
        ]);

        $resultsSheet = $spreadsheet->createSheet();
        $resultsSheet->setTitle('Test Results');
        $resultsSheet->fromArray([
            ['Test Name', 'Category', 'Statistic', 'P-value'],
        ]);

        $row = 2;
        foreach ($report->analysisJob->results->where('test_category', '!=', 'profile') as $result) {
            $resultsSheet->fromArray([
                [
                    $result->test_name,
                    $result->test_category,
                    $result->test_statistic,
                    $result->p_value,
                ],
            ], null, "A{$row}");
            $row++;
        }

        $profile = $report->analysisJob->results->firstWhere('test_category', 'profile');
        if ($profile !== null) {
            $profileSheet = $spreadsheet->createSheet();
            $profileSheet->setTitle('Data Profile');
            $profileSheet->setCellValue('A1', json_encode($profile->raw_output, JSON_PRETTY_PRINT));
        }

        $tempFile = tempnam(sys_get_temp_dir(), 'doctorstats_report_');
        if ($tempFile === false) {
            throw new RuntimeException('Unable to create temporary Excel file.');
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($tempFile);

        $path = sprintf('reports/%s/report.xlsx', $report->analysisJob->job_id);
        Storage::disk(config('filesystems.default'))->put($path, file_get_contents($tempFile) ?: '');
        unlink($tempFile);

        $report->update(['excel_path' => $path]);

        return $path;
    }

    private function renderPdf(string $html): string
    {
        $options = new Options;
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4');
        $dompdf->render();

        return $dompdf->output();
    }
}
