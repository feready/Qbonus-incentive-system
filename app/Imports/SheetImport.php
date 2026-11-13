<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class SheetImport implements ToCollection
{
    public function __construct(
        private BonusImport $parent,
        private string $sheetName
    ) {
    }

    public function collection(Collection $rows)
    {
        $this->parent->sheets[$this->sheetName] = $rows;

        /*
         * Pertahankan behaviour lama:
         * $import->rows tetap berisi
         * sheet Pengajuan Insentif.
         */
        if ($this->sheetName === 'Pengajuan Insentif') {
            $this->parent->rows = $rows;
        }
    }
}