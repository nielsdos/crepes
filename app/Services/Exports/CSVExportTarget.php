<?php

namespace App\Services\Exports;

use PhpOffice\PhpSpreadsheet\IOFactory;

class CSVExportTarget extends AbstractPhpSpreadsheetExportTarget implements ExportTarget
{
    public function export(Exportable $e): string|null
    {
        return $this->exportInternal($e, IOFactory::WRITER_CSV);
    }
}
