<?php

namespace App\Domains\Patients\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Patient extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'patients';

    protected $fillable = [
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
     * Preparado para Módulo 3 – Órdenes.
     * La tabla orders tendrá patient_id → patients.id
     */
    public function orders(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Domains\Orders\Models\Order::class, 'patient_id');
    }
}
