<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GuestAppointmentAccountCreated extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $patientName,
        public readonly string $temporaryPassword,
        public readonly Appointment $appointment,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your STBC appointment account details',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.guest-appointment-account-created',
        );
    }
}
