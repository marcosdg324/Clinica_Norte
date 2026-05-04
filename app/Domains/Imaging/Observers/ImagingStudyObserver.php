<?php

namespace App\Domains\Imaging\Observers;

use App\Domains\Imaging\Models\ImagingStudy;

class ImagingStudyObserver
{
    /**
     * Cuando un estudio cambia de estado, verifica si la orden asociada
     * debe actualizarse automáticamente.
     *
     * Regla: si ya no quedan estudios en estados activos
     * (programado/paciente_presente/en_proceso), y al menos uno fue
     * completado exitosamente → la orden pasa a "completada".
     */
    public function updated(ImagingStudy $study): void
    {
        if (! $study->wasChanged('status')) {
            return;
        }

        $order = $study->order;

        if (! $order) {
            return;
        }

        // Ignorar si la orden ya está en estado terminal
        if (in_array($order->status, ['cancelada', 'completada'])) {
            return;
        }

        $totalStudies = ImagingStudy::where('order_id', $order->id)->count();

        if ($totalStudies === 0) {
            return;
        }

        // Estudios que aún están en proceso activo (no terminales)
        $pendingStudies = ImagingStudy::where('order_id', $order->id)
            ->whereIn('status', ['programado', 'paciente_presente', 'en_proceso'])
            ->count();

        // Si todos los estudios están en estado terminal (completado o cancelado)
        // y al menos uno fue completado exitosamente → orden completada
        if ($pendingStudies === 0) {
            $completedStudies = ImagingStudy::where('order_id', $order->id)
                ->where('status', 'completado')
                ->count();

            if ($completedStudies > 0) {
                $order->update(['status' => 'completada']);
            }
        }
    }
}
