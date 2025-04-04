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

    public $numbers;
    public $serial_number;
    public $user;
    public $status;
    public $fecha_sorteo;


    /**
     * Create a new message instance.
     */
    public function __construct($numbers, $serial_number, $status, $user, $fecha_sorteo)
    {
        $this->numbers = $numbers;
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
        // Generar la imagen del cartón
        $generator = new BingoCardGenerator();
        $image = $generator->generateCardImage(
            $this->numbers,
            $this->serial_number,
            $this->status
        );

        // Guardar temporalmente la imagen
        $imagePath = storage_path('app/public/cards/' . $this->serial_number . '.png');
        $image->save($imagePath);

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
            Attachment::fromPath(storage_path('app/public/cards/' . $this->serial_number . '.png')),
        ];
    }
}
