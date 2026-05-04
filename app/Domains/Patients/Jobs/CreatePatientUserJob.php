<?php

namespace App\Domains\Patients\Jobs;

use App\Domains\Patients\Mail\PatientWelcomeMail;
use App\Domains\Patients\Models\Patient;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CreatePatientUserJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Patient $patient) {}

    public function handle(): void
    {
        // Evitar duplicados si el paciente ya tiene usuario vinculado
        if ($this->patient->user_id !== null) {
            return;
        }

        // Evitar duplicados si ya existe un User con ese email
        if (User::where('email', $this->patient->email)->exists()) {
            $existingUser = User::where('email', $this->patient->email)->first();
            $this->patient->updateQuietly(['user_id' => $existingUser->id]);

            return;
        }

        // Contraseña temporal: año + CN + últimos 4 dígitos de la CI
        $last4 = substr($this->patient->ci, -4);
        $tempPassword = now()->year.'CN'.$last4;

        // Crear el usuario
        $user = User::create([
            'name' => $this->patient->first_name.' '.$this->patient->last_name,
            'email' => $this->patient->email,
            'password' => Hash::make($tempPassword),
        ]);

        // Asignar rol Paciente
        $user->assignRole('Paciente');

        // Vincular user_id al paciente (sin disparar observer de nuevo)
        $this->patient->updateQuietly(['user_id' => $user->id]);

        // Enviar mail de bienvenida con credenciales
        try {
            Mail::to($user->email)->send(new PatientWelcomeMail($this->patient, $tempPassword));
        } catch (\Throwable $e) {
            Log::error('Error al enviar mail de bienvenida al paciente.', [
                'patient_id' => $this->patient->id,
                'email' => $this->patient->email,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
