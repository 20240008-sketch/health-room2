<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NursingVisit extends Model
{
    protected $fillable = [
        'date',
        'time',
        'student_id',
        'category',
        'type',
        'type_detail',
        'absence_reason',
        'occurrence_time',
        'subject',
        'club',
        'event',
        'breakfast',
        'bowel_movement',
        'treatment',
        'injury_location',
        'injury_place',
        'surgical_treatment',
        'treatment_notes',
    ];

    protected $casts = [
        'date' => 'date',
        'time' => 'datetime:H:i',
    ];

    /**
     * 学生とのリレーション
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }
}
