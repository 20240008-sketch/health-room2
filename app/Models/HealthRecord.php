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
        'height',
        'weight'
    ];

    protected $casts = [
        'student_id' => 'integer',
        'year' => 'integer',
        'height' => 'decimal:2',
        'weight' => 'decimal:2',
    ];

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
}