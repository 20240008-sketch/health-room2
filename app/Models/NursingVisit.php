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
        'occurrence_time',
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
        return $this->belongsTo(Student::class);
    }
}
