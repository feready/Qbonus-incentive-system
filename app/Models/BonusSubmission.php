<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BonusSubmission extends Model
{
    protected $fillable = [
        'user_id',
        'original_filename',
        'file_path',
        'store_code',
        'store_name',
        'validation_status',
        'calculation_status',
        'total_rows',
        'match_count',
        'difference_count',
        'error_count',
        'validation_summary',
        'calculation_summary',
        'results',
        'decision_status',
        'appeal_reason',
        'decided_by',
        'decided_at',
        'master_file_version_id',
    ];

    protected function casts(): array
    {
        return [
            'validation_summary' => 'array',
            'calculation_summary' => 'array',
            'results' => 'array',
            'decided_at' => 'datetime',
        ];

        
    }
public function masterFileVersion(): BelongsTo
{
    return $this->belongsTo(MasterFileVersion::class);
}
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function decisionMaker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(
            BonusSubmissionItem::class,
            'bonus_submission_id'
        );
    }
}
