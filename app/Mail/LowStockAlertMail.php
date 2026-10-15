<?php

namespace App\Mail;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\{Content, Envelope};
use Illuminate\Queue\SerializesModels;

class LowStockAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Product $product, public string $managerEmail) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '⚠️ Low Stock Alert: ' . $this->product->name,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.low-stock');
    }
}