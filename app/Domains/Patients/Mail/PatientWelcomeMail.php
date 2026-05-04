<?php

namespace App\Domains\Patients\Mail;

use App\Domains\Patients\Models\Patient;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PatientWelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Patient $patient,
        public readonly string $tempPassword,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Bienvenido/a a Clínica Norte — Tus credenciales de acceso',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.patients.welcome',
            with: [
                'patient' => $this->patient,
                'tempPassword' => $this->tempPassword,
                'portalUrl' => url('/'),   // TODO: actualizar cuando el portal esté listo
            ],
        );
    }
}
