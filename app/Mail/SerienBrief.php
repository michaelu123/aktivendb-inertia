<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Crypt;

class SerienBrief extends Mailable
{
    use Queueable, SerializesModels;
    public string $sbUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(public string $member_id)
    {
        $url = request()->url();
        $x = strpos($url, "/livewire");
        $this->sbUrl = substr($url, 0, $x) . "/sb/" . Crypt::encryptString($member_id);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address("aktivenmanagement@adfc-muenchen.de"),
            subject: 'SerienBrief',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mail.serienbrief',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
