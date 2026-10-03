<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TransactionReceipt extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public array $receipt,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'E-Receipt — ' . $this->receipt['transaction_id'],
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.transaction_receipt',
        );
    }
}