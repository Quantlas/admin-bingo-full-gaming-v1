<?php

namespace App\Mail;

use App\Services\BingoCardGenerator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;

class AcceptPaymentNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $filename;
    public $serial_number;
    public $user;
    public $status;
    public $fecha_sorteo;


    /**
     * Create a new message instance.
     */
    public function __construct($filename, $serial_number, $status, $user, $fecha_sorteo)
    {
        $this->filename = $filename;
        $this->serial_number = $serial_number;
        $this->status = $status;
        $this->user = $user;
        $this->fecha_sorteo = $fecha_sorteo;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(
                config('mail.from.address'), // Email
                'Bingo Full Gaming' // Nombre que quieres mostrar
            ),
            subject: 'BFG - Compra confirmada - ' . $this->serial_number,
            to: [new Address($this->user->email, $this->user->name)]
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.compra-aceptada'
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [
            Attachment::fromPath('images/paid-cards/' . $this->filename),
        ];
    }
}
