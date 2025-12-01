<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserCredentialsMail extends Mailable
{
    use Queueable, SerializesModels;

    public $nombreUsuario;
    public $email;
    public $password;
    public $rol;

    /**
     * Create a new message instance.
     */
    public function __construct(string $nombreUsuario, string $email, string $password, string $rol)
    {
        $this->nombreUsuario = $nombreUsuario;
        $this->email = $email;
        $this->password = $password;
        $this->rol = $rol;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Credenciales de acceso - Oh Sansi',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.user-credentials',
            with: [
                'nombreUsuario' => $this->nombreUsuario,
                'email' => $this->email,
                'password' => $this->password,
                'rol' => $this->rol,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
