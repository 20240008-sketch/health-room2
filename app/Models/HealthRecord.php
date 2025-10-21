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
        'hearing_right'
    ];

    protected $casts = [
        'student_id' => 'integer',
        'year' => 'integer',
        'measured_date' => 'date',
        'height' => 'decimal:2',
        'weight' => 'decimal:2',
        'vision_left' => 'decimal:2',
        'vision_right' => 'decimal:2',
        'vision_left_corrected' => 'decimal:2',
        'vision_right_corrected' => 'decimal:2',
    ];

    protected $appends = ['bmi', 'academic_year'];

    /**
     * Get the student that owns the health record.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
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