<?php

namespace App\Domains\Catalog\Observers;

use App\Domains\Catalog\Models\ExamCategory;
use Illuminate\Support\Facades\Log;

class ExamCategoryObserver
{
    /**
     * Registra en log cuando se crea una nueva categoría de examen.
     */
    public function created(ExamCategory $examCategory): void
    {
        Log::info('Catalog: nueva categoría creada.', [
            'id' => $examCategory->id,
            'name' => $examCategory->name,
            'type' => $examCategory->type,
        ]);
    }

    /**
     * Registra en log cuando una categoría es desactivada.
     */
    public function updated(ExamCategory $examCategory): void
    {
        if ($examCategory->wasChanged('is_active') && ! $examCategory->is_active) {
            Log::warning('Catalog: categoría desactivada.', [
                'id' => $examCategory->id,
                'name' => $examCategory->name,
                'type' => $examCategory->type,
            ]);
        }
    }
}
