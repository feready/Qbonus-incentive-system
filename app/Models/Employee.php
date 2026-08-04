<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = [
        'uuid',
        'nik',
        'name',
        'division_id',
        'position_id',
        'bank_id',
        'account_number',
        'npwp',
        'join_date',
        'active',
    ];

    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }
}