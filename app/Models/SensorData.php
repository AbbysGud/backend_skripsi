<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SensorData extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'rfid_tag',
        'weight',
        'previous_weight',
        'created_at',
        'updated_at',
    ];
}
