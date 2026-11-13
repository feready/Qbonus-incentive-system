<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BonusSubmissionItem extends Model
{
    protected $fillable = [
        'bonus_submission_id',
        'excel_row',
        'employee_id',
        'employee_name',
        'employee_code',
        'source_data',
        'calculation_data',
        'excel_final',
        'qbonus_final',
        'difference',
        'calculation_status',
        'decision_status',
        'appeal_reason',
        'decided_by',
        'decided_at',
    ];

    protected function casts(): array
    {
        return [
            'source_data' => 'array',
            'calculation_data' => 'array',
            'excel_final' => 'decimal:6',
            'qbonus_final' => 'decimal:6',
            'difference' => 'decimal:6',
            'decided_at' => 'datetime',
        ];
    }

    public function submission(): BelongsTo
    {
        return $this->belongsTo(BonusSubmission::class, 'bonus_submission_id');
    }

    public function decisionMaker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }
}
