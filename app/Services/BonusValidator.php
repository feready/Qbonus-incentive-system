<?php

namespace App\Services;

use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use DateTimeImmutable;
use Throwable;

class BonusValidator
{
    private array $requiredHeaders = [
        'Bln', 'ID', 'Name', 'Join Date', 'Usia', 'End Date',
        'Job Title Name', 'Location Name', 'Code', 'ZONA', 'CAT',
        'cZone', 'cCat', 'C_Zone', 'C_Cat',
        'Ach Zona', 'Ach Cat', 'Ach PB', 'Ach DSL', 'Ach AWT', 'Ach NEMS',
        'KPI Zona', 'KPI Cat', 'KPI PB', 'KPI DSL', 'KPI AWT', 'KPI NEMS',
        'Total KPI', 'Tier Ach', 'Insentif Basic', 'Ins Basic x KPI',
        'Final Insentif', 'Note (jika ada)',
    ];

   private array $textColumns = [
    'ID',
    'Name',
    'Job Title Name',
    'Location Name',
    'Code',
    'ZONA',
    'CAT',
    'cZone',
    'cCat',
    'C_Zone',
    'C_Cat',
    'Tier Ach',
    'Note (jika ada)',
];
      private array $numericColumns = [
    'Bln',
    'Usia',
    'Ach Zona',
    'Ach Cat',
    'Ach PB',
    'Ach DSL',
    'Ach AWT',
    'Ach NEMS',
    'KPI Zona',
    'KPI Cat',
    'KPI PB',
    'KPI DSL',
    'KPI AWT',
    'KPI NEMS',
    'Total KPI',
    'Insentif Basic',
    'Ins Basic x KPI',
    'Final Insentif',
];  

   
    private array $percentageColumns = [
        'Ach Zona', 'Ach Cat', 'Ach PB',
        'Ach DSL', 'Ach AWT', 'Ach NEMS',
        'KPI Zona', 'KPI Cat', 'KPI PB',
        'KPI DSL', 'KPI AWT', 'KPI NEMS',
        'Total KPI',
    ];

    /**
     * Normalisasi nilai Excel sebelum validasi dan kalkulasi.
     */
    private function normalizeValue(string $column, mixed $value): mixed
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_string($value) && trim($value) === '') {
            return null;
        }

        // Kolom identitas selalu diperlakukan sebagai teks.
        if (in_array($column, $this->textColumns, true)) {
            return trim((string) $value);
        }

        // Normalisasi tanggal Excel.
        if (in_array($column, ['Join Date', 'End Date'], true)) {
            return $this->normalizeDate($value);
        }

        // Normalisasi angka.
        if (in_array($column, $this->numericColumns, true)) {
            return $this->normalizeNumber($column, $value);
        }

        return $value;
    }

    /**
     * Mengubah angka berbagai format menjadi angka PHP.
     *
     * Contoh:
     * 2.150.554,56 -> 2150554.56
     * 2,150,554.56 -> 2150554.56
     * 111,77%      -> 1.1177
     */
    private function normalizeNumber(
        string $column,
        mixed $value
    ): mixed {
        if (is_int($value) || is_float($value)) {
            return $value;
        }

        $raw = trim((string) $value);

        if ($raw === '') {
            return null;
        }

        $isPercent = str_contains($raw, '%');

        // Hilangkan simbol mata uang dan spasi.
        $raw = str_ireplace(
            ['IDR', 'RP', "\xc2\xa0", ' '],
            '',
            $raw
        );

        $isNegative = false;

        if (preg_match('/^\((.*)\)$/', $raw, $matches)) {
            $isNegative = true;
            $raw = $matches[1];
        }

        // Hanya pertahankan digit, separator, dan tanda minus.
        $raw = preg_replace('/[^0-9,.\-]/u', '', $raw);

        if ($raw === '' || $raw === '-') {
            return $value;
        }

        $hasComma = str_contains($raw, ',');
        $hasDot = str_contains($raw, '.');

        if ($hasComma && $hasDot) {

            // Separator terakhir dianggap sebagai desimal.
            if (strrpos($raw, ',') > strrpos($raw, '.')) {
                $raw = str_replace('.', '', $raw);
                $raw = str_replace(',', '.', $raw);
            } else {
                $raw = str_replace(',', '', $raw);
            }

        } elseif ($hasComma) {

            if (
                in_array($column, $this->percentageColumns, true) &&
                !preg_match('/,\d{3}(,\d{3})+$/', $raw)
            ) {
                $raw = str_replace(',', '.', $raw);
            } elseif (preg_match('/,\d{3}(,\d{3})*$/', $raw)) {
                $raw = str_replace(',', '', $raw);
            } else {
                $raw = str_replace(',', '.', $raw);
            }

        } elseif ($hasDot) {

            if (
                in_array($column, $this->percentageColumns, true) &&
                !preg_match('/\.\d{3}(\.\d{3})+$/', $raw)
            ) {
                // Achievement/KPI dapat memiliki desimal.
            } elseif (preg_match('/\.\d{3}(\.\d{3})*$/', $raw)) {
                $raw = str_replace('.', '', $raw);
            }

        }

        if (!is_numeric($raw)) {
            return $value;
        }

        $number = (float) $raw;

        if ($isPercent) {
            $number /= 100;
        }

        if ($isNegative) {
            $number *= -1;
        }

        return $number;
    }

    /**
     * Mengubah tanggal Excel maupun tanggal teks menjadi Y-m-d.
     */
    private function normalizeDate(mixed $value): mixed
    {
        if (is_numeric($value)) {
            $serial = (float) $value;

            if ($serial >= 20000 && $serial <= 80000) {
                try {
                    return ExcelDate::excelToDateTimeObject($serial)
                        ->format('Y-m-d');
                } catch (Throwable $e) {
                    return $value;
                }
            }
        }

        $raw = trim((string) $value);

        // Format tanggal Indonesia didahulukan.
        $formats = [
            '!d/m/Y',
            '!d-m-Y',
            '!Y-m-d',
            '!d.m.Y',
        ];

        foreach ($formats as $format) {
            $date = DateTimeImmutable::createFromFormat($format, $raw);

            if ($date !== false) {
                $errors = DateTimeImmutable::getLastErrors();

                if (
                    $errors === false ||
                    ($errors['warning_count'] === 0 &&
                     $errors['error_count'] === 0)
                ) {
                    return $date->format('Y-m-d');
                }
            }
        }

        $timestamp = strtotime($raw);

        if ($timestamp !== false) {
            return date('Y-m-d', $timestamp);
        }

        return $value;
    }

    public function validate(Collection $rows): array
    {
        if ($rows->isEmpty()) {
            return [
                'status' => 'ERROR',
                'message' => 'File Excel kosong.',
                'errors' => ['File tidak memiliki data.'],
                'rows' => [],
            ];
        }

        $headers = $rows->first()
            ->map(fn ($value) => trim((string) $value))
            ->values()
            ->toArray();

        $missingHeaders = array_diff(
            $this->requiredHeaders,
            $headers
        );

        if (!empty($missingHeaders)) {
            return [
                'status' => 'ERROR',
                'message' => 'Struktur file tidak sesuai template QBonus.',
                'errors' => array_values($missingHeaders),
                'rows' => [],
            ];
        }

        $headerMap = array_flip($headers);

        $resultRows = [];
        $usedIds = [];

        foreach ($rows->skip(1) as $rowIndex => $row) {

            $rawData = array_values($row->toArray());

            $data = [];

            foreach ($headers as $index => $header) {
                $data[$index] = $this->normalizeValue(
                    $header,
                    $rawData[$index] ?? null
                );
            }

            // Lewati baris yang benar-benar kosong.
            if (collect($data)->every(
                fn ($value) => $value === null || $value === ''
            )) {
                continue;
            }

            $errors = [];
            $warnings = [];

            $get = function (string $column) use ($data, $headerMap) {
                $index = $headerMap[$column] ?? null;

                return $index !== null
                    ? ($data[$index] ?? null)
                    : null;
            };

            // REQUIRED FIELD
            $requiredFields = [
                'Bln', 'ID', 'Name', 'Join Date',
                'Job Title Name', 'Location Name',
                'Code', 'ZONA', 'CAT',
            ];

            foreach ($requiredFields as $field) {
                $value = $get($field);

                if ($value === null || trim((string) $value) === '') {
                    $errors[] = "$field kosong.";
                }
            }

            // DUPLICATE ID
            $id = trim((string) $get('ID'));

            if ($id !== '') {
                if (in_array($id, $usedIds, true)) {
                    $errors[] = "ID $id terdeteksi duplikat.";
                } else {
                    $usedIds[] = $id;
                }
            }

            // NUMERIC VALIDATION
            foreach ($this->numericColumns as $field) {
                $value = $get($field);

                if ($value === null || $value === '') {
                    continue;
                }

                if (!is_numeric($value)) {
                    $errors[] = "$field harus berupa angka.";
                }
            }

            // DATE VALIDATION
            foreach (['Join Date', 'End Date'] as $field) {
                $value = $get($field);

                if ($value !== null && strtotime((string) $value) === false) {
                    $errors[] = "$field tidak valid.";
                }
            }

            // WARNING
            $finalInsentif = $get('Final Insentif');

            if ($finalInsentif === null || $finalInsentif === '') {
                $warnings[] = 'Final Insentif belum terisi.';
            }

            $status = !empty($errors)
                ? 'ERROR'
                : (!empty($warnings) ? 'WARNING' : 'VALID');

            $resultRows[] = [
                'excel_row' => $rowIndex + 2,
                'data' => $data,
                'status' => $status,
                'errors' => $errors,
                'warnings' => $warnings,
            ];
        }

        $valid = collect($resultRows)->where('status', 'VALID')->count();
        $warning = collect($resultRows)->where('status', 'WARNING')->count();
        $error = collect($resultRows)->where('status', 'ERROR')->count();

        return [
            'status' => $error > 0
                ? 'ERROR'
                : ($warning > 0 ? 'WARNING' : 'VALID'),

            'message' => 'Validasi dan normalisasi selesai.',
            'total' => count($resultRows),
            'valid' => $valid,
            'warning' => $warning,
            'error' => $error,
            'rows' => $resultRows,
        ];
    }
}
