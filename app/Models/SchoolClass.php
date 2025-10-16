<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolClass extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'class_id',
        'class_name', 
        'grade',
        'kumi',
        'year'
    ];

    protected $casts = [
        'grade' => 'integer',
        'kumi' => 'integer',
        'year' => 'integer',
    ];

    protected $appends = ['name', 'display_name', 'academic_year', 'is_active', 'class_number'];

    /**
     * Get the students for the school class.
     */
    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'class_id', 'class_id');
    }

    /**
     * Get the class display name.
     */
    public function getDisplayNameAttribute(): string
    {
        return "{$this->year}年度 {$this->grade}年{$this->kumi}組";
    }

    /**
     * Get the class name (alias for class_name).
     */
    public function getNameAttribute(): string
    {
        return $this->class_name;
    }

    /**
     * Get the students count.
     * 既にSQLで計算された値があればそれを使用、なければリレーションから計算
     */
    public function getStudentsCountAttribute(): int
    {
        // 既に属性として設定されている場合（SQLクエリで取得した場合）
        if (isset($this->attributes['students_count'])) {
            return (int) $this->attributes['students_count'];
        }
        
        // リレーションがロードされている場合
        if ($this->relationLoaded('students')) {
            return $this->students->count();
        }
        
        // それ以外は0を返す
        return 0;
    }

    /**
     * Get the academic year (alias for year).
     */
    public function getAcademicYearAttribute(): int
    {
        return $this->year;
    }

    /**
     * Check if the class is active (current year).
     */
    public function getIsActiveAttribute(): bool
    {
        return $this->year == date('Y');
    }

    /**
     * Get the class number (alias for kumi).
     */
    public function getClassNumberAttribute(): ?int
    {
        return $this->kumi;
    }
}
