<?php

namespace App\Mail\User;

use App\Models\User;
use App\Models\Application;
use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Barryvdh\DomPDF\Pdf;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Contracts\Queue\ShouldQueue;

class TransactionReceipt extends Mailable
{
    use Queueable, SerializesModels;

    public Transaction $transaction;
    public Application $application;
    public Pdf $pdf;
    public string $receiptUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(Transaction $transaction, Application $application, Pdf $pdf, string $receiptUrl)
    {
        $this->transaction = $transaction;
        $this->application = $application;
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
            view: 'mail.user.transaction-receipt',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {

        // $pdf = Pdf::loadView('components.pdfs.transaction-success', [
        //     'transaction' => $this->transaction,
        //     'application' => $this->application,
        // ])->setOptions([
        //     'isRemoteEnabled' => true,
        //     'isHtml5ParserEnabled' => true,
        // ]);

        return [
            Attachment::fromData(
                fn() => $this->pdf->output(),
                'invoice.pdf'
            )->withMime('application/pdf'),
        ];
    }
}
