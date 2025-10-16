<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'name_kana',
        'student_id',
        'student_number',
        'gender',
        'birth_date',
        'class_id',
        'grade_id',
        'status',
        'phone',
        'emergency_contact',
        'address',
        'allergies',
        'medical_conditions',
        'medications',
        'notes'
    ];

    protected $casts = [
        'student_number' => 'integer',
        'class_id' => 'string',
        'birth_date' => 'date',
    ];

    /**
     * Get the school class that owns the student.
     */
    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id', 'class_id');
    }

    /**
     * Get the health records for the student.
     */
    public function healthRecords(): HasMany
    {
        return $this->hasMany(HealthRecord::class);
    }

    /**
     * Get the student's full display name.
     */
    public function getFullDisplayNameAttribute(): string
    {
        return "{$this->name} ({$this->kana})";
    }

    /**
     * Get the sex display name.
     */
    public function getSexDisplayAttribute(): string
    {
        return $this->sex === 'male' ? '男' : '女';
    }

    /**
     * Get the latest health record for the student.
     */
    public function latestHealthRecord(): HasMany
    {
        return $this->hasMany(HealthRecord::class)->latest('measured_date');
    }

    /**
     * Get the latest health record data.
     */
    public function getLatestHealthRecordAttribute(): ?HealthRecord
    {
        return $this->healthRecords()->latest('measured_date')->first();
    }

    /**
     * Get health records for a specific academic year.
     */
    public function healthRecordsForYear(int $academicYear): HasMany
    {
        return $this->hasMany(HealthRecord::class)->where('academic_year', $academicYear);
    }
}
