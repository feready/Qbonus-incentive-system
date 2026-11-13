<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BonusSubmissionSummaryExport implements WithMultipleSheets
{
    private array $summary;
    private $submissions;
    private $nonMatches;

    public function __construct(
        array $summary,
        $submissions,
        $nonMatches
    ) {
        $this->summary = $summary;
        $this->submissions = $submissions;
        $this->nonMatches = $nonMatches;
    }

    public function sheets(): array
    {
        return [
            new BonusSummaryOverviewSheet(
                $this->summary,
                $this->submissions
            ),
            new BonusSummaryNonMatchSheet(
                $this->nonMatches
            ),
        ];
    }
}

class BonusSummaryOverviewSheet implements
    FromArray,
    WithTitle,
    ShouldAutoSize,
    WithStyles
{
    private array $summary;
    private $submissions;

    public function __construct(array $summary, $submissions)
    {
        $this->summary = $summary;
        $this->submissions = $submissions;
    }

    public function title(): string
    {
        return 'Ringkasan & Riwayat';
    }

    public function array(): array
    {
        $rows = [
            ['QBONUS - SUBMISSION SUMMARY'],
            ['Tanggal Export', now()->format('d/m/Y H:i')],
            [],
            ['RINGKASAN'],
            ['Total Submission', $this->summary['total_submissions'] ?? 0],
            ['Total Karyawan', $this->summary['total_employees'] ?? 0],
            ['MATCH', $this->summary['match'] ?? 0],
            ['DIFFERENCE', $this->summary['difference'] ?? 0],
            ['ERROR', $this->summary['error'] ?? 0],
            [],
            ['RIWAYAT SUBMISSION'],
            [
                'No',
                'Tanggal Upload',
                'Store',
                'Kode Store',
                'Nama File',
                'Total Karyawan',
                'MATCH',
                'DIFFERENCE',
                'ERROR',
                'Status',
            ],
        ];

        foreach ($this->submissions as $index => $submission) {
            $rows[] = [
                $index + 1,
                $submission->created_at
                    ? $submission->created_at->format('d/m/Y H:i')
                    : '-',
                $submission->store_name ?? '-',
                $submission->store_code ?? '-',
                $submission->original_filename ?? '-',
                $submission->total_rows ?? 0,
                $submission->match_count ?? 0,
                $submission->difference_count ?? 0,
                $submission->error_count ?? 0,
                strtoupper($submission->calculation_status ?? 'PENDING'),
            ];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 16,
                ],
            ],
            4 => [
                'font' => [
                    'bold' => true,
                ],
            ],
            11 => [
                'font' => [
                    'bold' => true,
                ],
            ],
            12 => [
                'font' => [
                    'bold' => true,
                ],
            ],
        ];
    }
}

class BonusSummaryNonMatchSheet implements
    FromArray,
    WithTitle,
    ShouldAutoSize,
    WithStyles,
    WithColumnFormatting
{
    private $nonMatches;

    public function __construct($nonMatches)
    {
        $this->nonMatches = $nonMatches;
    }

    public function title(): string
    {
        return 'Detail Non-Match';
    }

    public function array(): array
    {
        $rows = [
            ['QBONUS - DETAIL DIFFERENCE & ERROR'],
            ['Tanggal Export', now()->format('d/m/Y H:i')],
            [],
            [
                'No',
                'Tanggal Upload',
                'Store',
                'Kode Store',
                'Nama Karyawan',
                'ID Karyawan',
                'Nama File',
                'Excel Final (Rp)',
                'QBonus Final (Rp)',
                'Selisih (Rp)',
                'Status',
                'Keputusan',
                'Alasan Appeal',
                'Tanggal Keputusan',
            ],
        ];

        foreach ($this->nonMatches as $index => $item) {
            $submission = $item->submission;

            $rows[] = [
                $index + 1,
                $submission && $submission->created_at
                    ? $submission->created_at->format('d/m/Y H:i')
                    : '-',
                $submission->store_name ?? '-',
                $submission->store_code ?? '-',
                $item->employee_name ?? '-',
                $item->employee_id ?? '-',
                $submission->original_filename ?? '-',
                $item->excel_final !== null
                    ? (float) $item->excel_final
                    : null,
                $item->qbonus_final !== null
                    ? (float) $item->qbonus_final
                    : null,
                $item->difference !== null
                    ? (float) $item->difference
                    : null,
                strtoupper($item->calculation_status ?? 'ERROR'),
                strtoupper($item->decision_status ?? 'PENDING'),
                $item->appeal_reason ?? '-',
                $item->decided_at
                    ? $item->decided_at->format('d/m/Y H:i')
                    : '-',
            ];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 16,
                ],
            ],
            4 => [
                'font' => [
                    'bold' => true,
                ],
            ],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'H' => '#,##0.00;[Red]-#,##0.00',
            'I' => '#,##0.00;[Red]-#,##0.00',
            'J' => '#,##0.00;[Red]-#,##0.00',
        ];
    }
}
