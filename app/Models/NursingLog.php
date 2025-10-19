<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NursingLog extends Model
{
    protected $fillable = [
        'date',
        'year',
        'month',
        'day',
        'weather',
        'temperature',
        'humidity',
        'absence',
        'visits',
        'school_events',
        'water_quality',
        'chlorine',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'absence' => 'array',
        'visits' => 'array',
    ];
}
