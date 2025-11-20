<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Booking Request Received</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #284544; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background-color: #f9f9f9; }
        .booking-details { background-color: white; padding: 20px; margin: 20px 0; border-radius: 5px; }
        .detail-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #eee; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 14px; }
        .button { background-color: #B08141; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 20px 0; }
        .status-badge { background-color: #f59e0b; color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; }
        .important-note { background-color: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px; margin: 20px 0; }
        .price-match { background-color: #d1fae5; border-left: 4px solid #10b981; padding: 15px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Booking Request Received</h1>
            <p>Thank you for choosing us!</p>
        </div>

        <div class="content">
            <p>Dear {{ $booking->first_name }} {{ $booking->last_name }},</p>
            
            <p>We have successfully received your booking request! Here are the details we have on file:</p>

            <div class="booking-details">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <h3 style="margin: 0;">{{ $property->title }}</h3>
                    <span class="status-badge">PENDING CONFIRMATION</span>
                </div>
                
                <div class="detail-row">
                    <strong>Booking Reference:</strong>
                    <span>#{{ $booking->id }}</span>
                </div>
                
                <div class="detail-row">
                    <strong>Check-in Date:</strong>
                    <span>{{ $checkInDate }}</span>
                </div>
                
                <div class="detail-row">
                    <strong>Check-out Date:</strong>
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
                    <span>${{ number_format($booking->total_price, 2) }} <small>(subject to change)</small></span>
                </div>
                
                @if($booking->special_requests)
                <div class="detail-row">
                    <strong>Special Requests:</strong>
                    <span>{{ $booking->special_requests }}</span>
                </div>
                @endif
            </div>

            <div class="important-note">
                <p><strong>⏳ Booking Status: Pending Confirmation</strong></p>
                <p>Your booking request is currently pending while we confirm availability with the accommodation provider. We will contact you within 24 hours with confirmation and final details.</p>
                <p><strong>Please note:</strong> The quoted price is subject to change based on final confirmation from the property. Any price adjustments will be communicated before final booking confirmation.</p>
            </div>

            <div class="price-match">
                <p><strong>💰 Price Match Guarantee</strong></p>
                <p>Found a lower price elsewhere? We offer a price match guarantee! Contact us with details of the lower price and we'll do our best to match or beat it.</p>
            </div>

            <div style="text-align: center;">
                <a href="{{ route('properties.show', $property) }}" class="button">
                    View Property Details
                </a>
            </div>

            <p><strong>What Happens Next?</strong></p>
            <ul>
                <li><strong>Within 24 hours:</strong> We'll confirm availability with the accommodation provider</li>
                <li><strong>Upon confirmation:</strong> You'll receive final booking details and payment instructions</li>
                <li><strong>Before arrival:</strong> Check-in details and property contact information will be provided</li>
                <li><strong>Need changes?</strong> Contact us immediately if you need to modify your request</li>
            </ul>

            <p>If you have any questions about your booking request or need immediate assistance, please don't hesitate to contact us using the details below.</p>

            <p>Thank you for choosing Bali Villa Spot!</p>
        </div>

        <div class="footer">
            <p>
                <strong>Contact Us:</strong><br>
                Email: <a href="mailto:{{ $businessEmail }}">{{ $businessEmail }}</a><br>
                Phone: <a href="tel:{{ $businessPhone }}">{{ $businessPhone }}</a>
            </p>
            <p>&copy; {{ date('Y') }} Bali Villa Spot. All rights reserved.</p>
        </div>
    </div>
</body>
</html>