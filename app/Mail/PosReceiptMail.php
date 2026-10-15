<?php

namespace App\Mail;

use App\Models\PosTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\{Content, Envelope};
use Illuminate\Queue\SerializesModels;

class PosReceiptMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public PosTransaction $transaction) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Receipt ' . $this->transaction->receipt_no,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.receipt');
    }
}