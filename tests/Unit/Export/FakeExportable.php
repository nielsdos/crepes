<?php

namespace Tests\Unit\Export;

use App\Services\Exports\Exportable;
use Illuminate\Support\Collection;

class FakeExportable implements Exportable
{
    public function heading(): array
    {
        return ['Col1', 'Col2', 'Col3'];
    }

    public function map(mixed $data): array
    {
        return [$data[0] * 2, ! $data[1], $data[2]];
    }

    public function collection(): Collection
    {
        return new Collection([
            [1, true, 'abc'],
            [3, false, 'def'],
            [8, true, 'ghi'],
        ]);
    }
}
