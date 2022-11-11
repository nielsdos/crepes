<?php

namespace Tests\Unit\Export;

use App\Services\Exports\AbstractPhpSpreadsheetExportTarget;
use App\Services\Exports\Exportable;
use App\Services\Exports\ExportTarget;
use Tests\TestCase;

class InvalidExportTarget extends AbstractPhpSpreadsheetExportTarget implements ExportTarget
{
    public function export(Exportable $e): string|null
    {
        return $this->exportInternal($e, '');
    }
}

class InvalidExportTest extends TestCase
{
    public function testCsvExport(): void
    {
        $fakeExportable = new FakeExportable();
        $target = new InvalidExportTarget();
        $this->assertNull($target->export($fakeExportable));
    }
}
