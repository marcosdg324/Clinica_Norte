<?php

namespace App\Domains\Catalog\Models;

use App\Domains\Orders\Models\Order;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Exam extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'exams';

    protected $fillable = [
        'name',
        'exam_category_id',
        'type',
        'price',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    // ── Relaciones ─────────────────────────────────────────────────────────────

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExamCategory::class, 'exam_category_id');
    }

    public function requirements(): HasMany
    {
        return $this->hasMany(ExamRequirement::class, 'exam_id');
    }

    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'order_exam', 'exam_id', 'order_id');
    }
}
