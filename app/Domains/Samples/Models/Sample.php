<?php

namespace App\Domains\Samples\Models;

use App\Domains\Orders\Models\Exam;
use App\Domains\Orders\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sample extends Model
{
    use HasFactory;

    protected $table = 'samples';

    protected $fillable = [
        'order_id',
        'exam_id',
        'barcode',
        'status',
        'collected_at',
        'collected_by',
        'location',
        'notes',
    ];

    protected $casts = [
        'collected_at' => 'datetime',
    ];

    // ── Relaciones ─────────────────────────────────────────────────────────────

    public function order(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function exam(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Exam::class, 'exam_id');
    }

    public function collectedBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'collected_by');
    }

    public function statusHistories(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SampleStatusHistory::class, 'sample_id')->latest();
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    public static function generateBarcode(): string
    {
        do {
            $code = 'SMP-' . strtoupper(substr(uniqid('', true), -8));
        } while (static::where('barcode', $code)->exists());

        return $code;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'recibida'    => 'Recibida',
            'en_analisis' => 'En análisis',
            'procesada'   => 'Procesada',
            'rechazada'   => 'Rechazada',
            default       => ucfirst($this->status),
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'recibida'    => 'info',
            'en_analisis' => 'warning',
            'procesada'   => 'success',
            'rechazada'   => 'danger',
            default       => 'gray',
        };
    }
}
