<?php

namespace App\Services\Exports;

interface Exportable
{
    /**
     * Gets the heading.
     *
     * @return array<string>
     */
    public function heading(): array;

    /**
     * Maps data from object to array of columns in a row.
     *
     * @param  mixed  $data
     * @return array<mixed>
     */
    public function map(mixed $data): array;

    /**
     * Gets the collection of data.
     *
     * @return \Illuminate\Support\Collection<int, mixed>
     */
    public function collection(): \Illuminate\Support\Collection;
}
