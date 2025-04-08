<?php

namespace App\Mail\Partner;

use App\Models\Prospect;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewProspectRegistered extends Mailable
{
    use Queueable, SerializesModels;

    public Prospect $prospect;

    public function __construct(Prospect $prospect)
    {
        $this->prospect = $prospect;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Prospect Registered',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.partner.new-prospect-registered',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
