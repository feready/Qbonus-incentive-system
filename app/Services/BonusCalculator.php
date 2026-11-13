<?php

namespace App\Services;

class BonusCalculator
{
    private array $weights = [
        'Ach Zona' => 0.30,
        'Ach Cat'  => 0.20,
        'Ach PB'   => 0.15,
        'Ach DSL'  => 0.15,
        'Ach AWT'  => 0.15,
        'Ach NEMS' => 0.05,
    ];

    public function __construct(
        private ReferenceWorkbookService $reference
    ) {}

    public function calculate(array $row): array
    {
        // Ambil enam achievement dari workbook referensi
        $achievements = $this->reference->getKpiValues(
            $row['Bln'],
            $row['Code'],
            $row['ZONA'],
            $row['CAT']
        );

        $row['Ach Zona'] = $achievements['ach_zona'];
        $row['Ach Cat'] = $achievements['ach_cat'];
        $row['Ach PB'] = $achievements['ach_pb'];
        $row['Ach DSL'] = $achievements['ach_dsl'];
        $row['Ach AWT'] = $achievements['ach_awt'];
        $row['Ach NEMS'] = $achievements['ach_nems'];

        // Hitung KPI berbobot
        $kpi = [];

        foreach ($this->weights as $achievement => $weight) {
            $kpiColumn = str_replace('Ach ', 'KPI ', $achievement);

            $kpi[$kpiColumn] =
                (float) $row[$achievement] * $weight;
        }

        $totalKpi = array_sum($kpi);

        // Tentukan tier berdasarkan Ach Zona
        $achZona = (float) $row['Ach Zona'];

        if ($achZona >= 1.10) {
            $tier = '>110%';
        } elseif ($achZona >= 1.05) {
            $tier = '>105%';
        } elseif ($achZona >= 1.00) {
            $tier = '>100%';
        } else {
            $tier = '>0%';
        }

        // Ambil Insentif Basic berdasarkan Code + Tier
        $basic = $this->reference->getInsentifBasic(
            $row['Code'],
            $tier
        );

        // Hitung Insentif Basic x KPI
        $basicKpi = $basic * $totalKpi;

        // Hitung Final Insentif
        $usia = (float) $row['Usia'];
        $final = $usia < 90 ? 0 : $basicKpi;

        return [
            ...$achievements,
            'KPI Zona' => $kpi['KPI Zona'],
            'KPI Cat' => $kpi['KPI Cat'],
            'KPI PB' => $kpi['KPI PB'],
            'KPI DSL' => $kpi['KPI DSL'],
            'KPI AWT' => $kpi['KPI AWT'],
            'KPI NEMS' => $kpi['KPI NEMS'],
            'Total KPI' => $totalKpi,
            'Tier Ach' => $tier,
            'Insentif Basic' => $basic,
            'Ins Basic x KPI' => $basicKpi,
            'Final Insentif QBonus' => $final,
        ];
    }
}
