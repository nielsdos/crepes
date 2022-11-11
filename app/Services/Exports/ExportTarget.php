<?php

namespace App\Services\Exports;

interface ExportTarget
{
    /**
     * Exports.
     *
     * @param  Exportable  $e
     * @return string|null temporary filename
     */
    public function export(Exportable $e): string|null;
}
