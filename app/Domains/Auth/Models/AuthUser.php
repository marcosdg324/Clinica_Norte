<?php

namespace App\Domains\Auth\Models;

use App\Models\User;

/**
 * AuthUser — representación del Usuario dentro del dominio Auth.
 * Hereda todos los traits y contratos de App\Models\User
 * (HasRoles, FilamentUser, Notifiable, HasFactory).
 */
class AuthUser extends User
{
    /**
     * Fuerza el uso de la misma tabla que el modelo base.
     */
    protected $table = 'users';
}
