<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuhuData extends Model
{
    use HasFactory;

    protected $fillable = [
        'temperature',
        'humidity',
        'device_id',
        'created_at',
        'updated_at',
    ];
}
