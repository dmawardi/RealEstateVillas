<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>New Booking Request</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #1f2937; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background-color: #f9f9f9; }
        .booking-details { background-color: white; padding: 20px; margin: 20px 0; border-radius: 5px; border: 1px solid #e5e7eb; }
        .detail-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #f3f4f6; }
        .detail-row:last-child { border-bottom: none; }
        .guest-details { background-color: #f0f9ff; padding: 15px; margin: 15px 0; border-radius: 5px; }
        .action-buttons { text-align: center; margin: 20px 0; }
        .button { background-color: #3b82f6; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 5px; }
        .button-secondary { background-color: #6b7280; }
        .status-badge { background-color: #f59e0b; color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; }
        .priority-high { background-color: #fef2f2; border-left: 4px solid #dc2626; padding: 15px; margin: 20px 0; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🏠 New Booking Request</h1>
            <p>Admin Notification</p>
        </div>

        <div class="content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2>Booking #{{ $booking->id }}</h2>
                <span class="status-badge">{{ strtoupper($booking->status) }}</span>
            </div>

            <div class="guest-details">
                <h3>Guest Information</h3>
                <div class="detail-row">
                    <strong>Name:</strong>
                    <span>{{ $booking->first_name }} {{ $booking->last_name }}</span>
                </div>
                <div class="detail-row">
                    <strong>Email:</strong>
                    <span><a href="mailto:{{ $booking->email }}">{{ $booking->email }}</a></span>
                </div>
                <div class="detail-row">
                    <strong>Phone:</strong>
                    <span><a href="tel:{{ $booking->phone }}">{{ $booking->phone ?? 'Not provided' }}</a></span>
                </div>
                <div class="detail-row">
                    <strong>Source:</strong>
                    <span>{{ ucfirst($booking->source) }}</span>
                </div>
            </div>

            <div class="booking-details">
                <h3>Property & Booking Details</h3>
                <div class="detail-row">
                    <strong>Property:</strong>
                    <span>{{ $property->title }}</span>
                </div>
                <div class="detail-row">
                    <strong>Location:</strong>
                    <span>{{ $property->district }}, {{ $property->regency }}</span>
                </div>
                <div class="detail-row">
                    <strong>Check-in:</strong>
                    <span>{{ $checkInDate }}</span>
                </div>
                <div class="detail-row">
                    <strong>Check-out:</strong>
                    <span>{{ $checkOutDate }}</span>
                </div>
                <div class="detail-row">
                    <strong>Duration:</strong>
                    <span>{{ $nights }} night{{ $nights !== 1 ? 's' : '' }}</span>
                </div>
                <div class="detail-row">
                    <strong>Guests:</strong>
                    <span>{{ $booking->number_of_guests }}</span>
                </div>
                @if($booking->number_of_rooms)
                <div class="detail-row">
                    <strong>Rooms:</strong>
                    <span>{{ $booking->number_of_rooms }}</span>
                </div>
                @endif
                <div class="detail-row">
                    <strong>Quoted Price:</strong>
                    <span>IDR {{ number_format($booking->total_price, 2) }}</span>
                </div>
                @if($booking->commission_rate)
                <div class="detail-row">
                    <strong>Commission ({{ $booking->commission_rate }}%):</strong>
                    <span>IDR {{ number_format($booking->commission_amount ?? 0, 2) }}</span>
                </div>
                @endif
            </div>

            @if($booking->special_requests)
            <div class="guest-details">
                <h3>Special Requests</h3>
                <p>{{ $booking->special_requests }}</p>
            </div>
            @endif

            <div class="priority-high">
                <h3>⚡ Action Required</h3>
                <p><strong>Response needed within 24 hours</strong></p>
                <p>Please confirm availability with the property owner and update the booking status accordingly.</p>
            </div>

            <div class="action-buttons">
                <a href="{{ $adminUrl }}" class="button">
                    View in Admin Panel
                </a>
                <a href="mailto:{{ $booking->email }}" class="button button-secondary">
                    Email Guest
                </a>
            </div>

            <h3>Next Steps:</h3>
            <ol>
                <li><strong>Confirm Availability:</strong> Contact property owner/manager</li>
                <li><strong>Update Status:</strong> Mark as confirmed or declined in admin panel</li>
                <li><strong>Send Follow-up:</strong> Notify guest of final confirmation and payment details</li>
                <li><strong>Schedule Reminders:</strong> Set up check-in preparation tasks</li>
            </ol>
        </div>

        <div class="footer">
            <p>This is an automated notification from the booking system.</p>
            <p>Booking received on {{ $booking->created_at->format('F j, Y \a\t g:i A') }}</p>
        </div>
    </div>
</body>
</html>