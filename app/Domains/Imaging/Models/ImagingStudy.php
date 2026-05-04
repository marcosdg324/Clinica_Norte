<?php

namespace App\Domains\Imaging\Models;

use App\Domains\Catalog\Models\Exam;
use App\Domains\Orders\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ImagingStudy extends Model
{
    protected $table = 'imaging_studies';

    protected $fillable = [
        'order_id',
        'exam_id',
        'equipment_id',
        'responsible_user_id',
        'study_code',
        'status',
        'result_file',
        'result_notes',
        'collected_at',
        'rejection_reason',
    ];

    protected $casts = [
        'collected_at' => 'datetime',
    ];

    // ── Relaciones ─────────────────────────────────────────────────────────────

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class, 'exam_id');
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(ImagingEquipment::class, 'equipment_id');
    }

    public function responsibleUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(ImagingStatusHistory::class, 'imaging_study_id')->latest();
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    public static function generateStudyCode(): string
    {
        do {
            $code = 'IMG-'.strtoupper(substr(uniqid('', true), -8));
        } while (static::where('study_code', $code)->exists());

        return $code;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'programado' => 'Programado',
            'paciente_presente' => 'Paciente presente',
            'en_proceso' => 'En proceso',
            'completado' => 'Completado',
            'cancelado' => 'Cancelado',
            default => ucfirst($this->status),
        };
    }
}
