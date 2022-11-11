<?php

namespace Tests\Feature;

use App\Http\Controllers\ExportFileType;
use App\Http\Controllers\ExportResponseCreator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;
use Tests\Unit\Export\FakeExportable;

class ExportResponseCreatorTest extends TestCase
{
    use ExportResponseCreator;

    public function testExportNull(): void
    {
        $response = $this->createRawExportResponse(null, 'my-file', 'my-content');
        $this->assertNull($response);
    }

    public function testExcelExport(): void
    {
        $response = $this->createExportResponse(new FakeExportable, 'my-excel-export', ExportFileType::Excel);
        $this->assertEquals('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $response->headers->get('Content-Type'));
        $this->assertEquals('private', $response->headers->get('Cache-Control'));
        $this->assertEquals('attachment; filename=my-excel-export.xlsx', $response->headers->get('content-disposition'));
        $file = $response->getFile();

        $spreadsheet = IOFactory::load($file->getRealPath());
        $this->assertGreaterThan(0, count($spreadsheet->getActiveSheet()->toArray()));
    }

    public function testCSVExport(): void
    {
        $response = $this->createCSVExportResponse(new FakeExportable, 'my-csv-export', ExportFileType::CSV);
        $this->assertEquals('text/csv', $response->headers->get('Content-Type'));
        $this->assertEquals('private', $response->headers->get('Cache-Control'));
        $this->assertEquals('attachment; filename=my-csv-export.csv', $response->headers->get('content-disposition'));
        $file = $response->getFile();

        $spreadsheet = IOFactory::load($file->getRealPath());
        $this->assertGreaterThan(0, count($spreadsheet->getActiveSheet()->toArray()));
    }
}
