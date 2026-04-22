<?php

namespace App\Domains\Samples\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class SampleStatusHistory extends Model
{
    protected $table = 'sample_status_histories';

    protected $fillable = [
        'sample_id',
        'old_status',
        'new_status',
        'changed_by',
        'notes',
    ];

    // ── Relaciones ─────────────────────────────────────────────────────────────

    public function sample(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Sample::class, 'sample_id');
    }

    public function changedBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    public function getStatusLabelAttribute(string $status): string
    {
        return match ($status) {
            'recibida'    => 'Recibida',
            'en_analisis' => 'En análisis',
            'procesada'   => 'Procesada',
            'rechazada'   => 'Rechazada',
            default       => ucfirst($status),
        };
    }
}
