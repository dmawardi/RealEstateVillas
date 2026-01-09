<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingReceivedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Booking $booking;

    /**
     * Create a new message instance.
     */
    public function __construct(Booking $booking)
    {
        $this->booking = $booking;        
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: config('app.mail_from_address'),
            subject: 'Booking Received - ' . $this->booking->property->title,
            replyTo: config('app.mail_from_address'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.booking-received',
            with: [
                'booking' => $this->booking,
                'property' => $this->booking->property,
                'checkInDate' => \Carbon\Carbon::parse($this->booking->check_in_date)->format('F j, Y'),
                'checkOutDate' => \Carbon\Carbon::parse($this->booking->check_out_date)->format('F j, Y'),
                'nights' => \Carbon\Carbon::parse($this->booking->check_in_date)
                    ->diffInDays(\Carbon\Carbon::parse($this->booking->check_out_date)),
                'businessPhone' => config('app.business_phone'),
                'businessEmail' => config('app.business_email'),
            ]
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
