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

    /**
     * Get the students for the school class.
     */
    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'class_id');
    }

    /**
     * Get the class display name.
     */
    public function getDisplayNameAttribute(): string
    {
        return "{$this->year}年度 {$this->grade}年{$this->kumi}組";
    }
}
