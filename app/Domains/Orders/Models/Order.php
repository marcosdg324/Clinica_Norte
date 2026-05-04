<?php

namespace App\Domains\Orders\Models;

use App\Domains\Catalog\Models\Exam;
use App\Domains\Imaging\Models\ImagingEquipment;
use App\Domains\Patients\Models\Patient;
use App\Domains\Samples\Models\Sample;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'orders';

    protected $fillable = [
        'patient_id',
        'order_number',
        'status',
        'type',
        'scheduled_date',
        'scheduled_time',
        'receptionist_id',
        'responsible_user_id',
        'equipment_id',
    ];

    protected $casts = [
        'type' => 'string',
        'scheduled_date' => 'date',
    ];

    // ── Relaciones ─────────────────────────────────────────────────────────────

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function receptionist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receptionist_id');
    }

    /**
     * Profesional responsable: Bioquímico (órdenes laboratorio) o Tecnólogo de Imagen (órdenes imagen).
     */
    public function responsibleUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    public function exams(): BelongsToMany
    {
        return $this->belongsToMany(Exam::class, 'order_exam', 'order_id', 'exam_id');
    }

    public function samples(): HasMany
    {
        return $this->hasMany(Sample::class, 'order_id');
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(ImagingEquipment::class, 'equipment_id');
    }

    // ── Accessors ──────────────────────────────────────────────────────────────

    public function getIsAcceptedAttribute(): bool
    {
        return $this->responsible_user_id !== null;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pendiente' => 'Pendiente',
            'en_proceso' => 'En proceso',
            'completada' => 'Completada',
            'cancelada' => 'Cancelada',
            default => ucfirst($this->status),
        };
    }
}
