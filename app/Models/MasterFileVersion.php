<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterFileVersion extends Model
{
    protected $fillable = [
        'version_number',
        'original_filename',
        'file_path',
        'file_size',
        'file_sha256',
        'uploaded_by',
        'validation_status',
        'validation_summary',
        'status',
        'is_active',
        'activated_by',
        'activated_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'validation_summary' => 'array',
            'is_active' => 'boolean',
            'activated_at' => 'datetime',
        ];
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function activator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'activated_by');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(
            BonusSubmission::class,
            'master_file_version_id'
        );
    }
}
