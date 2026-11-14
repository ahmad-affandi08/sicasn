<?php

declare(strict_types=1);

namespace Src\Pengaturan\Infrastructure\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Src\Pengaturan\Domain\IdentitasPengirim;
use Src\Shared\Application\PesanEmail;

final class PesanMarkdown extends Mailable
{
    public function __construct(
        private readonly PesanEmail $pesan,
        private readonly IdentitasPengirim $pengirim,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address($this->pengirim->alamat, $this->pengirim->nama),
            replyTo: $this->pengirim->alamatBalas === null ? [] : [new Address($this->pengirim->alamatBalas)],
            subject: $this->pesan->subjek,
        );
    }

    public function content(): Content
    {
        return new Content(markdown: 'email.'.$this->pesan->tampilan, with: $this->pesan->data);
    }
}
