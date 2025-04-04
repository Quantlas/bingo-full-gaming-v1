<?php

namespace App\Mail;

use App\Models\Games;
use App\Models\Payment;
use App\Services\BingoCardGenerator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;

class BuyGameNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $numbers;
    public $serial_number;
    public $user;

    /**
     * Create a new message instance.
     */
    public function __construct($numbers, $serial_number, $user)
    {
        $this->numbers = $numbers;
        $this->serial_number = $serial_number;
        $this->user = $user;
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
            $this->serial_number
        );

        // Guardar temporalmente la imagen
        $imagePath = storage_path('app/public/cards/' . $this->serial_number . '.png');
        $image->save($imagePath);

        return new Envelope(
            from: new Address(
                config('mail.from.address'), // Email
                'Bingo Full Gaming' // Nombre que quieres mostrar
            ),
            subject: 'BFG - Datos de compra - ' . $this->serial_number,
            cc: [
                new Address(
                    'administrador@bingofullgaming.com'
                )
            ]
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.compra'
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
