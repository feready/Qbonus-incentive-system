<?php

namespace App\Services;

use App\Models\MasterFileVersion;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use RuntimeException;
use Throwable;

class ReferenceWorkbookService
{
    public const REQUIRED_SHEETS = [
        'Pengajuan Insentif',
        'Zona',
        'Category',
        'PB',
        'DSL',
        'AWT',
        'NEMS',
        'SVR SLS',
        'SVR PB',
        'DB ZONA',
        'BUDGET PB',
        'DB value Basic',
    ];

    private array $lookupTables = [];

    private ?string $filePath = null;

    private ?MasterFileVersion $activeVersion = null;

    private bool $activeVersionResolved = false;

    /**
     * Mengambil satu Master File yang sedang aktif.
     */
    public function getActiveVersion(): ?MasterFileVersion
    {
        if (!$this->activeVersionResolved) {
            $this->activeVersion = MasterFileVersion::query()
                ->where('is_active', true)
                ->where('status', 'ACTIVE')
                ->latest('activated_at')
                ->first();

            $this->activeVersionResolved = true;
        }

        return $this->activeVersion;
    }

    /**
     * Memvalidasi workbook baru tanpa mengaktifkannya.
     */
    public function validateFile(string $path): array
    {
        if (!is_file($path) || !is_readable($path)) {
            throw new RuntimeException(
                'File Master tidak ditemukan atau tidak dapat dibaca.'
            );
        }

        $spreadsheet = null;

        try {
            $reader = IOFactory::createReaderForFile($path);
            $spreadsheet = $reader->load($path);

            $sheetNames = $spreadsheet->getSheetNames();

            $missingSheets = array_values(array_diff(
                self::REQUIRED_SHEETS,
                $sheetNames
            ));

            if (!empty($missingSheets)) {
                throw new RuntimeException(
                    'Sheet wajib tidak ditemukan: '
                    . implode(', ', $missingSheets)
                );
            }

            $lookups = $this->buildLookupTables($spreadsheet);

            $lookupCounts = [];

            foreach ($lookups as $name => $values) {
                $lookupCounts[$name] = count($values);

                if (empty($values)) {
                    throw new RuntimeException(
                        "Tabel lookup {$name} kosong atau tidak dapat dibaca."
                    );
                }
            }

            $numericTables = [
                'Zona',
                'Category',
                'PB',
                'DSL',
                'AWT',
                'NEMS',
                'Basic',
            ];

            foreach ($numericTables as $tableName) {
                $validValues = 0;

                foreach ($lookups[$tableName] as $value) {
                    if (is_numeric($value)) {
                        $validValues++;
                    }
                }

                if ($validValues === 0) {
                    throw new RuntimeException(
                        "Tidak ditemukan nilai numerik pada lookup {$tableName}."
                    );
                }
            }

            return [
                'status' => 'VALID',
                'message' => 'Semua sheet wajib dan tabel lookup berhasil diperiksa.',
                'sheet_count' => count($sheetNames),
                'required_sheet_count' => count(self::REQUIRED_SHEETS),
                'sheet_names' => $sheetNames,
                'lookup_counts' => $lookupCounts,
                'store_count' => count($lookups['Stores']),
            ];

        } catch (Throwable $e) {
            if ($e instanceof RuntimeException) {
                throw $e;
            }

            throw new RuntimeException(
                'Workbook gagal divalidasi: ' . $e->getMessage(),
                0,
                $e
            );

        } finally {
            if ($spreadsheet !== null) {
                $spreadsheet->disconnectWorksheets();
            }
        }
    }

    /**
     * Memuat lookup dari Master File aktif.
     */
    public function load(?string $path = null): void
    {
        if ($path === null && !empty($this->lookupTables)) {
            return;
        }

        $resolvedPath = $path ?? $this->resolveActiveFilePath();

        if (!is_file($resolvedPath)) {
            throw new RuntimeException(
                'File Master Insentif tidak ditemukan.'
            );
        }

        $spreadsheet = null;

        try {
            $reader = IOFactory::createReaderForFile($resolvedPath);
            $spreadsheet = $reader->load($resolvedPath);

            $sheetNames = $spreadsheet->getSheetNames();

            $missingSheets = array_values(array_diff(
                self::REQUIRED_SHEETS,
                $sheetNames
            ));

            if (!empty($missingSheets)) {
                throw new RuntimeException(
                    'Master File aktif memiliki sheet yang tidak lengkap: '
                    . implode(', ', $missingSheets)
                );
            }

            $this->lookupTables = $this->buildLookupTables($spreadsheet);

            $this->filePath = $resolvedPath;

        } finally {
            if ($spreadsheet !== null) {
                $spreadsheet->disconnectWorksheets();
            }
        }
    }

    /**
     * Menentukan lokasi file Master aktif.
     */
    private function resolveActiveFilePath(): string
    {
        $activeVersion = $this->getActiveVersion();

        if ($activeVersion) {
            $disk = Storage::disk('local');

            if (!$disk->exists($activeVersion->file_path)) {
                throw new RuntimeException(
                    'File Master versi '
                    . $activeVersion->version_number
                    . ' tidak ditemukan di penyimpanan.'
                );
            }

            return $disk->path($activeVersion->file_path);
        }

        // Fallback untuk kompatibilitas instalasi lama.
        $legacyPath = storage_path(
            'app/private/references/incentive_source.xlsm'
        );

        if (file_exists($legacyPath)) {
            return $legacyPath;
        }

        throw new RuntimeException(
            'Belum ada Master File Insentif aktif.'
        );
    }

    /**
     * Membangun seluruh tabel lookup.
     */
    private function buildLookupTables($spreadsheet): array
    {
        return [
            'Zona' => $this->buildLookup(
                $spreadsheet->getSheetByName('Zona'),
                'F',
                'I',
                3
            ),

            'Category' => $this->buildLookup(
                $spreadsheet->getSheetByName('Category'),
                'G',
                'J',
                3
            ),

            'PB' => $this->buildLookup(
                $spreadsheet->getSheetByName('PB'),
                'G',
                'J',
                4
            ),

            'DSL' => $this->buildLookup(
                $spreadsheet->getSheetByName('DSL'),
                'E',
                'F',
                2
            ),

            'AWT' => $this->buildLookup(
                $spreadsheet->getSheetByName('AWT'),
                'E',
                'F',
                2
            ),

            'NEMS' => $this->buildLookup(
                $spreadsheet->getSheetByName('NEMS'),
                'D',
                'E',
                3
            ),

            'Basic' => $this->buildLookup(
                $spreadsheet->getSheetByName('DB value Basic'),
                'J',
                'K',
                3
            ),

            // Master toko: kolom C = Code, D = Store Name.
            'Stores' => $this->buildLookup(
                $spreadsheet->getSheetByName('AWT'),
                'C',
                'D',
                2
            ),
        ];
    }

    private function buildLookup(
        $sheet,
        string $keyColumn,
        string $valueColumn,
        int $startRow
    ): array {
        if ($sheet === null) {
            throw new RuntimeException(
                'Worksheet yang dibutuhkan tidak ditemukan.'
            );
        }

        $lookup = [];

        for (
            $row = $startRow;
            $row <= $sheet->getHighestRow();
            $row++
        ) {
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

    /**
     * Daftar toko dari Master File aktif.
     */
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

    /**
     * Mengambil enam achievement KPI.
     * Rumus lookup tetap mengikuti workbook sebelumnya.
     */
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
                'Zona',
                (string) $bulan . $cZone
            ),

            'ach_cat' => $this->findValue(
                'Category',
                (string) $bulan . $cCat
            ),

            'ach_pb' => $this->findValue(
                'PB',
                $cZone
            ),

            'ach_dsl' => $this->findValue(
                'DSL',
                $monthCode
            ),

            'ach_awt' => $this->findValue(
                'AWT',
                $monthCode
            ),

            'ach_nems' => $this->findValue(
                'NEMS',
                $monthCode
            ),
        ];
    }

    /**
     * Mengambil Insentif Basic berdasarkan Code + Tier.
     */
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

    private function findValue(
        string $sheet,
        string $key
    ): float {
        if (!array_key_exists($key, $this->lookupTables[$sheet])) {
            throw new RuntimeException(
                "Data lookup {$sheet} tidak ditemukan untuk key {$key}."
            );
        }

        $value = $this->lookupTables[$sheet][$key];

        if (!is_numeric($value)) {
            throw new RuntimeException(
                "Nilai lookup {$sheet} tidak valid untuk key {$key}."
            );
        }

        return (float) $value;
    }

    private function ensureLoaded(): void
    {
        if (empty($this->lookupTables)) {
            $this->load();
        }
    }
}
