<?php

namespace App\Mail\Partner;

use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Barryvdh\DomPDF\Pdf;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;

class TransactionReceipt extends Mailable
{
    use Queueable, SerializesModels;

    public Transaction $transaction;
    public Pdf $pdf;
    public string $receiptUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(Transaction $transaction, Pdf $pdf, string $receiptUrl)
    {
        $this->transaction = $transaction;
        $this->pdf = $pdf;
        $this->receiptUrl = $receiptUrl;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Transaction Receipt',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mail.partner.transaction-receipt',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
