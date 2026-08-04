<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;
class Division extends Model
{
    use HasUuid;
    protected $fillable = [
        'uuid',
        'code',
        'name',
        'description',
        'active'
    ];

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }
}