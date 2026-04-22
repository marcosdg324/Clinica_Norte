<?php

namespace App\Domains\Orders\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Exam extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'exams';

    protected $fillable = [
        'name',
        'type',
        'price',
        'description',
        'is_urgent_possible',
    ];

    protected $casts = [
        'price'              => 'decimal:2',
        'is_urgent_possible' => 'boolean',
    ];

    // ── Relaciones ─────────────────────────────────────────────────────────────

    public function requirements(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ExamRequirement::class, 'exam_id');
    }

    public function orders(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'order_exam', 'exam_id', 'order_id');
    }
}
