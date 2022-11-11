<?php

namespace Tests\Unit\Export;

use App\Services\Exports\ExcelExportTarget;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class ExcelExportTest extends TestCase
{
    public function testCsvExport(): void
    {
        $fakeExportable = new FakeExportable();
        $target = new ExcelExportTarget();
        $expectedData = $fakeExportable->collection()->map(fn ($data) => $fakeExportable->map($data));

        $filename = $target->export($fakeExportable);
        $this->assertNotNull($filename);

        $spreadsheet = IOFactory::load($filename);
        $sheet = $spreadsheet->getActiveSheet();
        $this->assertEquals([$fakeExportable->heading(), ...$expectedData], $sheet->toArray());
    }
}
