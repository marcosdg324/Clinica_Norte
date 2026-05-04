<?php

namespace App\Domains\Imaging\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImagingStatusHistory extends Model
{
    protected $table = 'imaging_status_histories';

    protected $fillable = [
        'imaging_study_id',
        'old_status',
        'new_status',
        'changed_by',
        'notes',
    ];

    // ── Relaciones ─────────────────────────────────────────────────────────────

    public function study(): BelongsTo
    {
        return $this->belongsTo(ImagingStudy::class, 'imaging_study_id');
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
