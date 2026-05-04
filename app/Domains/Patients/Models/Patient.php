<?php

namespace App\Domains\Patients\Models;

use App\Domains\Orders\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Patient extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'patients';

    protected $fillable = [
        'user_id',
        'ci',
        'first_name',
        'last_name',
        'birth_date',
        'gender',
        'phone',
        'email',
        'address',
        'medical_history_notes',
    ];

    protected $casts = [
        'birth_date' => 'date',
    ];

    // ── Accessors ──────────────────────────────────────────────────────────────

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getAgeAttribute(): int
    {
        return $this->birth_date->age;
    }

    // ── Relaciones ─────────────────────────────────────────────────────────────

    /**
     * Usuario del sistema vinculado a este paciente (rol Paciente).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Preparado para Módulo 3 – Órdenes.
     * La tabla orders tendrá patient_id → patients.id
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'patient_id');
    }
}
