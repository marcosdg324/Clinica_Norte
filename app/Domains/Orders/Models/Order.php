<?php

namespace App\Domains\Orders\Models;

use App\Domains\Patients\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'orders';

    protected $fillable = [
        'patient_id',
        'order_number',
        'status',
        'is_urgent',
        'scheduled_date',
        'scheduled_time',
        'receptionist_id',
    ];

    protected $casts = [
        'is_urgent'      => 'boolean',
        'scheduled_date' => 'date',
    ];

    // ── Relaciones ─────────────────────────────────────────────────────────────

    public function patient(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function receptionist(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'receptionist_id');
    }

    public function exams(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Exam::class, 'order_exam', 'order_id', 'exam_id');
    }

    // ── Accessors ──────────────────────────────────────────────────────────────

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pendiente'   => 'Pendiente',
            'en_proceso'  => 'En proceso',
            'completado'  => 'Completado',
            'cancelado'   => 'Cancelado',
            default       => ucfirst($this->status),
        };
    }
}
