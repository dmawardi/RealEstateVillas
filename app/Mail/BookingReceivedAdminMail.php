<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingReceivedAdminMail extends Mailable
{
    use Queueable, SerializesModels;

    public Booking $booking;

    public function __construct(Booking $booking)
    {
        $this->booking = $booking;        
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: config('app.mail_from_address'),
            subject: 'New Booking Request - ' . $this->booking->property->title . ' (#' . $this->booking->id . ')',
            replyTo: $this->booking->email ?? config('app.mail_from_address'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.booking-received-admin',
            with: [
                'booking' => $this->booking,
                'property' => $this->booking->property,
                'checkInDate' => \Carbon\Carbon::parse($this->booking->check_in_date)->format('F j, Y'),
                'checkOutDate' => \Carbon\Carbon::parse($this->booking->check_out_date)->format('F j, Y'),
                'nights' => \Carbon\Carbon::parse($this->booking->check_in_date)
                    ->diffInDays(\Carbon\Carbon::parse($this->booking->check_out_date)),
                'adminUrl' => route('admin.bookings.show', $this->booking->id),
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}