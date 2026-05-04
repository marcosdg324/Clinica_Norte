<?php

namespace App\Domains\Catalog\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExamParameter extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'exam_parameters';

    protected $fillable = [
        'exam_category_id',
        'name',
        'unit',
        'reference_min',
        'reference_max',
        'critical_min',
        'critical_max',
    ];

    protected $casts = [
        'reference_min' => 'decimal:4',
        'reference_max' => 'decimal:4',
        'critical_min' => 'decimal:4',
        'critical_max' => 'decimal:4',
    ];

    // ── Relaciones ─────────────────────────────────────────────────────────────

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExamCategory::class, 'exam_category_id');
    }
}
