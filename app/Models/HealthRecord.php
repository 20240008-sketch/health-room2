<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HealthRecord extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'student_id',
        'year',
        'measured_date',
        'height',
        'weight',
        'vision_left',
        'vision_right',
        'vision_left_corrected',
        'vision_right_corrected',
        'hearing_left',
        'hearing_right',
        'ophthalmology_result',
        'ophthalmology_exam_result',
        'ophthalmology_diagnosis',
        'ophthalmology_treatment',
        'otolaryngology_result',
        'internal_medicine_result',
        'hearing_test_result',
        'tuberculosis_test_result',
        'urine_test_result',
        'ecg_result',
        'dental_result'
    ];

    protected $casts = [
        'year' => 'integer',
        'measured_date' => 'date',
        'height' => 'decimal:2',
        'weight' => 'decimal:2',
        'hearing_left' => 'boolean',
        'hearing_right' => 'boolean',
    ];

    protected $appends = ['bmi', 'academic_year'];

    /**
     * Get the student that owns the health record.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    /**
     * Get the BMI value.
     */
    public function getBmiAttribute(): ?float
    {
        if ($this->height && $this->weight) {
            $heightM = $this->height / 100; // cm to m
            return round($this->weight / ($heightM * $heightM), 2);
        }
        return null;
    }

    /**
     * Get the academic year (alias for year).
     */
    public function getAcademicYearAttribute(): int
    {
        return $this->year;
    }
}