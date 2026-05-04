<?php

namespace App\Domains\Patients\Observers;

use App\Domains\Patients\Jobs\CreatePatientUserJob;
use App\Domains\Patients\Models\Patient;

class PatientObserver
{
    /**
     * Al crear un nuevo paciente, despacha el job que crea
     * su cuenta de usuario y le envía las credenciales por email.
     */
    public function created(Patient $patient): void
    {
        CreatePatientUserJob::dispatch($patient);
    }
}
