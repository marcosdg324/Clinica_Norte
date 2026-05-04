<?php

namespace App\Domains\Samples\Observers;

use App\Domains\Samples\Models\Sample;

class SampleObserver
{
    /**
     * Cuando una muestra cambia de estado, verifica si la orden asociada
     * debe actualizarse automáticamente.
     *
     * Regla: si ya no quedan muestras en estados pendientes (recibida/en_analisis),
     * la orden pasa a "completada" automáticamente.
     */
    public function updated(Sample $sample): void
    {
        if (! $sample->wasChanged('status')) {
            return;
        }

        $order = $sample->order;

        if (! $order) {
            return;
        }

        // Ignorar si la orden ya está cancelada o completada
        if (in_array($order->status, ['cancelada', 'completada'])) {
            return;
        }

        $totalSamples = $order->samples()->count();

        if ($totalSamples === 0) {
            return;
        }

        // Muestras que aún están en proceso activo (no terminales)
        $pendingSamples = $order->samples()
            ->whereIn('status', ['recibida', 'en_analisis'])
            ->count();

        // Si todas las muestras están en estado terminal (procesada o rechazada)
        // y al menos una fue procesada exitosamente → orden completada
        if ($pendingSamples === 0) {
            $processedSamples = $order->samples()
                ->where('status', 'procesada')
                ->count();

            if ($processedSamples > 0) {
                $order->update(['status' => 'completada']);
            }
        }
    }
}
