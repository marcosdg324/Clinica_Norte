<?php

namespace App\Domains\Imaging\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ImagingEquipment extends Model
{
    use SoftDeletes;

    protected $table = 'imaging_equipment';

    protected $fillable = [
        'name',
        'type',
        'description',
        'status',
    ];

    // ── Relaciones ─────────────────────────────────────────────────────────────

    public function studies(): HasMany
    {
        return $this->hasMany(ImagingStudy::class, 'equipment_id');
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'ecógrafo' => 'Ecógrafo',
            'rayos_x' => 'Rayos X',
            'tomógrafo' => 'Tomógrafo',
            'otro' => 'Otro',
            default => ucfirst($this->type),
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'disponible' => 'Disponible',
            'mantenimiento' => 'En mantenimiento',
            'fuera_de_servicio' => 'Fuera de servicio',
            default => ucfirst($this->status),
        };
    }
}
