<?php

namespace App\Mail;

use App\Models\Games;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;

class BuyGameNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $serial_number;
    public $user;
    public $card_path;

    /**
     * Create a new message instance.
     */
    public function __construct($serial_number, $user, $card_filename)
    {
        $this->serial_number = $serial_number;
        $this->user = $user;
        $this->card_path = public_path('images/pendding-cards/' . $card_filename);
    }


    /**
     * Descargar imagen remota a temporal si es una URL
     */
    protected function downloadRemoteImage($url)
    {
        $tempPath = tempnam(sys_get_temp_dir(), 'bingo_card') . '.webp';
        file_put_contents($tempPath, file_get_contents($url));
        return $tempPath;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(
                config('mail.from.address'),
                'Bingo Full Gaming'
            ),
            subject: 'BFG - Datos de compra - ' . $this->serial_number,
            cc: [
                new Address('administrador@bingofullgaming.com')
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
     */
    public function attachments(): array
    {
        return [
            Attachment::fromPath($this->card_path)
                ->as('carton-bingo.webp')
                ->withMime('image/webp'),
        ];
    }

    /**
     * Limpiar archivos temporales al destruir
     */
    public function __destruct()
    {
        if (str_starts_with($this->card_path, sys_get_temp_dir())) {
            @unlink($this->card_path);
        }
    }
}
