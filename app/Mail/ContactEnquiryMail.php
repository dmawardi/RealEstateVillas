<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;

class ContactEnquiryMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $enquiry;

    /**
     * Create a new message instance.
     */
    public function __construct(array $enquiry)
    {
        $this->enquiry = $enquiry;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Contact Enquiry - ' . str_replace('_', ' ', ucwords($this->enquiry['inquiry_type'])) . ' - ' . $this->enquiry['subject'],
            replyTo: [
                new Address($this->enquiry['email'], $this->enquiry['name']),
            ],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.contact-enquiry',
            with: ['enquiry' => $this->enquiry]
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
