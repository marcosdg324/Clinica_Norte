<?php

namespace App\Domains\Orders\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamRequirement extends Model
{
    use HasFactory;

    protected $table = 'exam_requirements';

    protected $fillable = [
        'exam_id',
        'description',
    ];

    // ── Relaciones ─────────────────────────────────────────────────────────────

    public function exam(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Exam::class, 'exam_id');
    }
}
