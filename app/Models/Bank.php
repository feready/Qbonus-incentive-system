<?php

namespace App\Models;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    use HasUuid;
    protected $fillable = [
        'uuid',
        'code',
        'name',
        'active',
    ];

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }
}