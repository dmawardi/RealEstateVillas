<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Booking Update Notification</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #dc2626; color: white; padding: 20px; text-align: center; }
        .header.status-change { background-color: #2563eb; }
        .header.date-change { background-color: #7c2d12; }
        .header.cancellation { background-color: #991b1b; }
        .content { padding: 20px; background-color: #f9f9f9; }
        .alert { padding: 15px; margin: 20px 0; border-radius: 5px; border-left: 4px solid #dc2626; background-color: #fef2f2; }
        .alert.warning { border-left-color: #d97706; background-color: #fffbeb; }
        .alert.info { border-left-color: #2563eb; background-color: #eff6ff; }
        .changes-table { background-color: white; border-radius: 5px; overflow: hidden; margin: 20px 0; }
        .change-row { display: flex; padding: 12px; border-bottom: 1px solid #e5e7eb; }
        .change-row:last-child { border-bottom: none; }
        .change-field { font-weight: bold; width: 30%; color: #374151; }
        .change-old { width: 35%; color: #dc2626; text-decoration: line-through; }
        .change-new { width: 35%; color: #059669; font-weight: bold; }
        .booking-summary { background-color: white; padding: 20px; margin: 20px 0; border-radius: 5px; }
        .detail-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #f3f4f6; }
        .action-buttons { text-align: center; margin: 20px 0; }
        .button { background-color: #3b82f6; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 5px; }
        .button-danger { background-color: #dc2626; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header {{ $updateType }}">
            @if($updateType === 'cancellation')
                <h1>🚫 Booking Cancellation</h1>
                <p>A booking has been cancelled</p>
            @elseif($updateType === 'status_change')
                <h1>📋 Status Update</h1>
                <p>Booking status has been changed</p>
            @elseif($updateType === 'date_change')
                <h1>📅 Date Modification</h1>
                <p>Booking dates have been updated</p>
            @else
                <h1>✏️ Booking Modified</h1>
                <p>Important booking details have been updated</p>
            @endif
        </div>

        <div class="content">
            @if($updateType === 'cancellation')
                <div class="alert">
                    <h3>⚠️ Immediate Action Required</h3>
                    <p><strong>This booking has been cancelled.</strong> Please verify if any deposits need to be refunded and update your calendar accordingly.</p>
                </div>
            @elseif($updateType === 'date_change')
                <div class="alert warning">
                    <h3>📅 Date Change Alert</h3>
                    <p><strong>The guest has modified their booking dates.</strong> Please confirm availability for the new dates and update any existing arrangements.</p>
                </div>
            @elseif($updateType === 'status_change')
                <div class="alert info">
                    <h3>📊 Status Update</h3>
                    <p>The booking status has been updated. Review the changes below and take any necessary actions.</p>
                </div>
            @endif

            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2>Booking #{{ $booking->id }}</h2>
                <span style="background-color: 
                    @if($booking->status === 'confirmed') #10b981
                    @elseif($booking->status === 'cancelled') #dc2626
                    @elseif($booking->status === 'pending') #f59e0b
                    @else #6b7280 @endif
                    ; color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: bold;">
                    {{ strtoupper($booking->status) }}
                </span>
            </div>

            <div class="changes-table">
                <div style="background-color: #f3f4f6; padding: 15px; font-weight: bold;">
                    Changes Made
                </div>
                @foreach($changes as $field => $change)
                    <div class="change-row">
                        <div class="change-field">{{ ucfirst(str_replace('_', ' ', $field)) }}:</div>
                        <div class="change-old">{{ $change['old'] ?? 'Not set' }}</div>
                        <div class="change-new">{{ $change['new'] ?? 'Not set' }}</div>
                    </div>
                @endforeach
            </div>

            <div class="booking-summary">
                <h3>Current Booking Details</h3>
                <div class="detail-row">
                    <strong>Guest:</strong>
                    <span>{{ $booking->first_name }} {{ $booking->last_name }}</span>
                </div>
                <div class="detail-row">
                    <strong>Email:</strong>
                    <span><a href="mailto:{{ $booking->email }}">{{ $booking->email }}</a></span>
                </div>
                <div class="detail-row">
                    <strong>Property:</strong>
                    <span>{{ $property->title }}</span>
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
                    <strong>Total Price:</strong>
                    <span>IDR {{ number_format($booking->total_price, 2) }}</span>
                </div>
            </div>

            <div class="action-buttons">
                <a href="{{ $adminUrl }}" class="button">
                    View Booking Details
                </a>
                <a href="mailto:{{ $booking->email }}" class="button">
                    Contact Guest
                </a>
                @if($updateType === 'cancellation')
                    <a href="{{ route('admin.bookings.edit', $booking->id) }}" class="button button-danger">
                        Process Cancellation
                    </a>
                @endif
            </div>

            @if($updateType === 'date_change')
                <h3>⏰ Next Steps for Date Changes:</h3>
                <ol>
                    <li><strong>Verify Availability:</strong> Check if new dates are available</li>
                    <li><strong>Update Calendar:</strong> Block/unblock dates as needed</li>
                    <li><strong>Recalculate Pricing:</strong> Check if price changes are needed</li>
                    <li><strong>Notify Property:</strong> Inform property manager of the changes</li>
                </ol>
            @elseif($updateType === 'cancellation')
                <h3>💰 Cancellation Checklist:</h3>
                <ol>
                    <li><strong>Check Cancellation Policy:</strong> Review terms for refund eligibility</li>
                    <li><strong>Process Refund:</strong> If applicable, initiate refund process</li>
                    <li><strong>Update Calendar:</strong> Release blocked dates</li>
                    <li><strong>Notify Property:</strong> Inform property manager of cancellation</li>
                </ol>
            @endif
        </div>

        <div class="footer">
            <p>Update processed on {{ now()->format('F j, Y \a\t g:i A') }}</p>
            <p>Original booking created: {{ $booking->created_at->format('F j, Y \a\t g:i A') }}</p>
        </div>
    </div>
</body>
</html>