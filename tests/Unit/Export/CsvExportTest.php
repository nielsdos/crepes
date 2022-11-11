<?php

namespace Tests\Unit\Export;

use App\Services\Exports\CSVExportTarget;
use Tests\TestCase;

class CsvExportTest extends TestCase
{
    public function testCsvExport(): void
    {
        $fakeExportable = new FakeExportable();
        $target = new CSVExportTarget();
        $expectedData = $fakeExportable->collection()->map(fn ($data) => $fakeExportable->map($data));

        $filename = $target->export($fakeExportable);
        $this->assertNotNull($filename);

        $this->assertNotFalse($handle = fopen($filename, 'r'));
        $gotData = [];
        $first = true;
        while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            if ($first) {
                $first = false;
            } else {
                $data[0] = (int) $data[0];
                $data[1] = match ($data[1]) {
                    'TRUE' => true,
                    'FALSE' => false,
                    default => '',
                };
                $gotData[] = $data;
            }
        }
        fclose($handle);

        $this->assertEquals($expectedData->toArray(), $gotData);
    }
}
