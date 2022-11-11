<?php

namespace App\Http\Controllers;

use App\Services\Exports\CSVExportTarget;
use App\Services\Exports\ExcelExportTarget;
use App\Services\Exports\Exportable;

trait ExportResponseCreator
{
    private function createExportResponse(Exportable $e, string $filename, ExportFileType $exportFileType): ?\Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        return match ($exportFileType) {
            ExportFileType::Excel => $this->createExcelExportResponse($e, $filename),
            ExportFileType::CSV => $this->createCSVExportResponse($e, $filename),
        };
    }

    private function createExcelExportResponse(Exportable $e, string $filename): ?\Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        return $this->createRawExportResponse(
            (new ExcelExportTarget)->export($e),
            $filename.'.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );
    }

    private function createCSVExportResponse(Exportable $e, string $filename): ?\Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        return $this->createRawExportResponse(
            (new CSVExportTarget)->export($e),
            $filename.'.csv',
            'text/csv'
        );
    }

    private function createRawExportResponse(?string $source, string $filename, string $contentType): ?\Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        if (! $source) {
            return null;
        }

        return response()->download($source, $filename, [
            'Content-Type' => $contentType,
        ])->deleteFileAfterSend(true)->setCache(['private' => true]);
    }
}
