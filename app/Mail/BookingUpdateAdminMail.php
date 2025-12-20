<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingUpdateAdminMail extends Mailable
{
    use Queueable, SerializesModels;

    public Booking $booking;
    public array $changes;
    public string $updateType;

    public function __construct(Booking $booking, array $changes, string $updateType = 'modification')
    {
        $this->booking = $booking;
        $this->changes = $changes;
        $this->updateType = $updateType;
    }

    public function envelope(): Envelope
    {
        $subjectPrefix = match($this->updateType) {
            'cancellation' => 'CANCELLATION',
            'status_change' => 'STATUS CHANGE',
            'date_change' => 'DATE CHANGE', 
            default => 'UPDATE'
        };

        return new Envelope(
            from: config('app.mail_from_address'),
            subject: $subjectPrefix . ' - Booking #' . $this->booking->id . ' - ' . $this->booking->property->title,
            replyTo: $this->booking->email ?? config('app.mail_from_address'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.booking-update-admin',
            with: [
                'booking' => $this->booking,
                'property' => $this->booking->property,
                'changes' => $this->changes,
                'updateType' => $this->updateType,
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