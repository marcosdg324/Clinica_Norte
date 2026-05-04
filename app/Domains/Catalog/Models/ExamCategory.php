<?php

namespace App\Domains\Catalog\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExamCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'exam_categories';

    protected $fillable = [
        'type',
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ── Relaciones ─────────────────────────────────────────────────────────────

    public function parameters(): HasMany
    {
        return $this->hasMany(ExamParameter::class, 'exam_category_id');
    }

    public function requirements(): HasManyThrough
    {
        return $this->hasManyThrough(
            ExamRequirement::class,
            Exam::class,
            'exam_category_id', // FK en exams → exam_categories
            'exam_id',          // FK en exam_requirements → exams
        );
    }

    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class, 'exam_category_id');
    }
}
