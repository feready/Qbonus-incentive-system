<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;
use RuntimeException;

class ReferenceWorkbookService
{
    private string $filePath;

    private array $lookupTables = [];

    public function __construct()
    {
        $this->filePath = storage_path(
            'app/private/references/incentive_source.xlsm'
        );
    }

    public function load(): void
    {
        if (!file_exists($this->filePath)) {
            throw new RuntimeException(
                'File workbook referensi tidak ditemukan.'
            );
        }

        $reader = IOFactory::createReaderForFile($this->filePath);
        $spreadsheet = $reader->load($this->filePath);

        $this->lookupTables = [
            'Zona' => $this->buildLookup(
                $spreadsheet->getSheetByName('Zona'), 'F', 'I', 3
            ),
            'Category' => $this->buildLookup(
                $spreadsheet->getSheetByName('Category'), 'G', 'J', 3
            ),
            'PB' => $this->buildLookup(
                $spreadsheet->getSheetByName('PB'), 'G', 'J', 4
            ),
            'DSL' => $this->buildLookup(
                $spreadsheet->getSheetByName('DSL'), 'E', 'F', 2
            ),
            'AWT' => $this->buildLookup(
                $spreadsheet->getSheetByName('AWT'), 'E', 'F', 2
            ),
            'NEMS' => $this->buildLookup(
                $spreadsheet->getSheetByName('NEMS'), 'D', 'E', 3
            ),
            'Basic' => $this->buildLookup(
                $spreadsheet->getSheetByName('DB value Basic'),
                'J', 'K', 3
            ),

            // Master toko: kolom C = Code, D = Store Name
            'Stores' => $this->buildLookup(
                $spreadsheet->getSheetByName('AWT'),
                'C',
                'D',
                2
            ),
        ];

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
    }

    private function buildLookup(
        $sheet,
        string $keyColumn,
        string $valueColumn,
        int $startRow
    ): array {
        $lookup = [];

        for ($row = $startRow; $row <= $sheet->getHighestRow(); $row++) {
            $key = trim((string) $sheet
                ->getCell($keyColumn . $row)
                ->getCalculatedValue());

            if ($key === '') {
                continue;
            }

            $lookup[$key] = $sheet
                ->getCell($valueColumn . $row)
                ->getCalculatedValue();
        }

        return $lookup;
    }

    public function getStores(): array
    {
        $this->ensureLoaded();

        $stores = [];

        foreach ($this->lookupTables['Stores'] as $code => $name) {
            $stores[] = [
                'code' => (string) $code,
                'name' => (string) $name,
            ];
        }

        usort(
            $stores,
            fn ($a, $b) => strcasecmp($a['name'], $b['name'])
        );

        return $stores;
    }

    public function getKpiValues(
        int|string $bulan,
        int|string $code,
        int|string $zona,
        int|string $category
    ): array {
        $this->ensureLoaded();

        $cZone = (string) $code . (string) $zona;
        $cCat = $cZone . (string) $category;
        $monthCode = (string) $bulan . (string) $code;

        return [
            'ach_zona' => $this->findValue(
                'Zona', (string) $bulan . $cZone
            ),
            'ach_cat' => $this->findValue(
                'Category', (string) $bulan . $cCat
            ),
            'ach_pb' => $this->findValue('PB', $cZone),
            'ach_dsl' => $this->findValue('DSL', $monthCode),
            'ach_awt' => $this->findValue('AWT', $monthCode),
            'ach_nems' => $this->findValue('NEMS', $monthCode),
        ];
    }

    public function getInsentifBasic(
        int|string $code,
        string $tier
    ): float {
        $this->ensureLoaded();

        return $this->findValue(
            'Basic',
            (string) $code . $tier
        );
    }

    private function findValue(string $sheet, string $key): float
    {
        if (!array_key_exists($key, $this->lookupTables[$sheet])) {
            throw new RuntimeException(
                "Data lookup {$sheet} tidak ditemukan untuk key {$key}."
            );
        }

        return (float) $this->lookupTables[$sheet][$key];
    }

    private function ensureLoaded(): void
    {
        if (empty($this->lookupTables)) {
            $this->load();
        }
    }
}
