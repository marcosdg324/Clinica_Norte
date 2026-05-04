<?php

namespace App\Support;

use App\Domains\Imaging\Models\ImagingStudy;
use App\Domains\Orders\Models\Order;
use App\Domains\Samples\Models\Sample;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Limita listados y acceso por registro a órdenes/muestras/estudios asignados al usuario
 * cuando actúa como Bioquímico (solo lab) o Tecnólogo de Imagen (solo imagen).
 */
final class ResponsibleClinicalStaffScoping
{
    public static function scopeOrderQueryForPanel(Builder $query): Builder
    {
        $user = auth()->user();
        if (! $user instanceof User) {
            return $query;
        }
        if ($user->hasRole('Bioquímico')) {
            return $query->where('type', 'laboratorio')->where('responsible_user_id', $user->id);
        }
        if ($user->hasRole('Tecnólogo de Imagen')) {
            return $query->where('type', 'imagen')->where('responsible_user_id', $user->id);
        }

        return $query;
    }

    public static function scopeSampleQueryForPanel(Builder $query): Builder
    {
        $user = auth()->user();
        if (! $user instanceof User || ! $user->hasRole('Bioquímico')) {
            return $query;
        }

        return $query->whereHas('order', function (Builder $q) use ($user): void {
            $q->where('type', 'laboratorio')->where('responsible_user_id', $user->id);
        });
    }

    public static function scopeImagingStudyQueryForPanel(Builder $query): Builder
    {
        $user = auth()->user();
        if (! $user instanceof User || ! $user->hasRole('Tecnólogo de Imagen')) {
            return $query;
        }

        return $query->whereHas('order', function (Builder $q) use ($user): void {
            $q->where('type', 'imagen')->where('responsible_user_id', $user->id);
        });
    }

    public static function userMayAccessOrderInPanel(User $user, Order $order): bool
    {
        if ($user->hasRole('Bioquímico')) {
            return $order->type === 'laboratorio'
                && (int) $order->responsible_user_id === (int) $user->id;
        }
        if ($user->hasRole('Tecnólogo de Imagen')) {
            return $order->type === 'imagen'
                && (int) $order->responsible_user_id === (int) $user->id;
        }

        return true;
    }

    public static function userMayAccessSampleInPanel(User $user, Sample $sample): bool
    {
        if (! $user->hasRole('Bioquímico')) {
            return true;
        }
        $order = $sample->relationLoaded('order') ? $sample->order : $sample->order()->first();

        return $order instanceof Order && self::userMayAccessOrderInPanel($user, $order);
    }

    public static function userMayAccessImagingStudyInPanel(User $user, ImagingStudy $study): bool
    {
        if (! $user->hasRole('Tecnólogo de Imagen')) {
            return true;
        }
        $order = $study->relationLoaded('order') ? $study->order : $study->order()->first();

        return $order instanceof Order && self::userMayAccessOrderInPanel($user, $order);
    }
}
