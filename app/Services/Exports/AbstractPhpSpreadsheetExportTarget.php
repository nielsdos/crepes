<?php

namespace App\Services\Exports;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Table;

abstract class AbstractPhpSpreadsheetExportTarget
{
    /**
     * Internal export function
     */
    protected function exportInternal(Exportable $e, string $writer): ?string
    {
        try {
            return $this->exportInternalThrows($e, $writer);
        } catch (\PhpOffice\PhpSpreadsheet\Exception|\PhpOffice\PhpSpreadsheet\Writer\Exception $exception) {
            \Log::error($exception);

            return null;
        }
    }

    /**
     * Internal export function
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    protected function exportInternalThrows(Exportable $e, string $writer): ?string
    {
        $tmpName = tempnam(sys_get_temp_dir(), 'courses-export');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->setActiveSheetIndex(0);

        $columnIndex = 0;
        foreach ($e->heading() as $columnIndex => $value) {
            $sheet->setCellValueByColumnAndRow($columnIndex + 1, 1, $value);
        }

        $rowIndex = 0;
        foreach ($e->collection() as $rowIndex => $entry) {
            $data = $e->map($entry);
            foreach ($data as $columnIndex => $value) {
                $sheet->setCellValueByColumnAndRow($columnIndex + 1, $rowIndex + 2, $value);
            }
        }

        $table = new Table([1, 1, $columnIndex + 1, $rowIndex + 2]);
        $tableStyle = new Table\TableStyle(Table\TableStyle::TABLE_STYLE_MEDIUM2);
        $tableStyle->setShowRowStripes(true);
        $tableStyle->setShowColumnStripes(true);
        $tableStyle->setShowFirstColumn(true);
        $tableStyle->setShowLastColumn(true);
        $sheet->addTable($table);

        for ($i = 0; $i < $columnIndex; $i++) {
            $sheet->getColumnDimensionByColumn($i + 1)->setAutoSize(true);
        }

        $writer = IOFactory::createWriter($spreadsheet, $writer);
        $writer->save($tmpName);

        return $tmpName;
    }
}
