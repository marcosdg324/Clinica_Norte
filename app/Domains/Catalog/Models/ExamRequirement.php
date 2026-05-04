<?php

namespace App\Domains\Catalog\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamRequirement extends Model
{
    use HasFactory;

    protected $table = 'exam_requirements';

    protected $fillable = [
        'exam_id',
        'description',
        'instructions',
    ];

    // ── Relaciones ─────────────────────────────────────────────────────────────

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class, 'exam_id');
    }
}
