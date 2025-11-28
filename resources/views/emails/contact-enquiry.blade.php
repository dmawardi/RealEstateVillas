<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Contact Enquiry</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            background-color: #f8f9fa;
        }
        .container {
            background-color: white;
            margin: 20px;
            padding: 0;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .content {
            padding: 30px;
        }
        .enquiry-type {
            display: inline-block;
            background-color: #dbeafe;
            color: #1e40af;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 20px;
            text-transform: uppercase;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        .info-item {
            background-color: #f8fafc;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #3b82f6;
        }
        .info-label {
            font-weight: 600;
            color: #374151;
            font-size: 14px;
            margin-bottom: 5px;
        }
        .info-value {
            color: #111827;
            font-size: 16px;
        }
        .message-section {
            background-color: #f9fafb;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            margin-top: 20px;
        }
        .message-section h3 {
            margin: 0 0 15px 0;
            color: #374151;
            font-size: 18px;
        }
        .message-content {
            background-color: white;
            padding: 15px;
            border-radius: 6px;
            border: 1px solid #d1d5db;
            white-space: pre-line;
            line-height: 1.6;
        }
        .property-details {
            background-color: #ecfdf5;
            border: 1px solid #a7f3d0;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .property-details h3 {
            color: #065f46;
            margin: 0 0 15px 0;
        }
        .footer {
            background-color: #f3f4f6;
            padding: 20px 30px;
            text-align: center;
            color: #6b7280;
            font-size: 14px;
        }
        .priority-high {
            background-color: #fef2f2;
            border-color: #fca5a5;
        }
        .priority-high .enquiry-type {
            background-color: #fecaca;
            color: #991b1b;
        }
        @media (max-width: 600px) {
            .info-grid {
                grid-template-columns: 1fr;
            }
            .container {
                margin: 10px;
            }
            .header, .content {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container {{ in_array($enquiry['inquiry_type'], ['villa_rental', 'property_sale']) ? 'priority-high' : '' }}">
        <div class="header">
            <h1>New Contact Enquiry</h1>
            <p style="margin: 10px 0 0 0; opacity: 0.9;">{{ now()->format('F j, Y \a\t g:i A') }}</p>
        </div>

        <div class="content">
            <div class="enquiry-type">
                {{ str_replace('_', ' ', $enquiry['inquiry_type']) }}
            </div>

            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Name</div>
                    <div class="info-value">{{ $enquiry['name'] }}</div>
                </div>

                <div class="info-item">
                    <div class="info-label">Email</div>
                    <div class="info-value">
                        <a href="mailto:{{ $enquiry['email'] }}" style="color: #3b82f6; text-decoration: none;">
                            {{ $enquiry['email'] }}
                        </a>
                    </div>
                </div>

                @if(!empty($enquiry['phone']))
                <div class="info-item">
                    <div class="info-label">Phone</div>
                    <div class="info-value">
                        <a href="tel:{{ $enquiry['phone'] }}" style="color: #3b82f6; text-decoration: none;">
                            {{ $enquiry['phone'] }}
                        </a>
                    </div>
                </div>
                @endif

                <div class="info-item">
                    <div class="info-label">Subject</div>
                    <div class="info-value">{{ $enquiry['subject'] }}</div>
                </div>
            </div>

            @if(in_array($enquiry['inquiry_type'], ['villa_rental', 'property_sale']) && (
                !empty($enquiry['property_interest']) || 
                !empty($enquiry['budget']) || 
                !empty($enquiry['travel_dates']) || 
                !empty($enquiry['guests'])
            ))
            <div class="property-details">
                <h3>
                    @if($enquiry['inquiry_type'] === 'villa_rental')
                        🏖️ Villa Rental Details
                    @else 
                        🏡 Property Purchase Details
                    @endif
                </h3>
                
                <div class="info-grid">
                    @if(!empty($enquiry['property_interest']))
                    <div class="info-item" style="background-color: white;">
                        <div class="info-label">
                            {{ $enquiry['inquiry_type'] === 'villa_rental' ? 'Preferred Area' : 'Location Interest' }}
                        </div>
                        <div class="info-value">{{ $enquiry['property_interest'] }}</div>
                    </div>
                    @endif

                    @if(!empty($enquiry['budget']))
                    <div class="info-item" style="background-color: white;">
                        <div class="info-label">Budget Range</div>
                        <div class="info-value">
                            @switch($enquiry['budget'])
                                @case('under_100') Under $100/night @break
                                @case('100_300') $100 - $300/night @break  
                                @case('300_500') $300 - $500/night @break
                                @case('500_1000') $500 - $1,000/night @break
                                @case('over_1000') Over $1,000/night @break
                                @case('purchase_under_100k') Under $100k @break
                                @case('purchase_100k_500k') $100k - $500k @break
                                @case('purchase_over_500k') Over $500k @break
                                @default {{ $enquiry['budget'] }} @break
                            @endswitch
                        </div>
                    </div>
                    @endif

                    @if(!empty($enquiry['travel_dates']) && $enquiry['inquiry_type'] === 'villa_rental')
                    <div class="info-item" style="background-color: white;">
                        <div class="info-label">Travel Dates</div>
                        <div class="info-value">{{ $enquiry['travel_dates'] }}</div>
                    </div>
                    @endif

                    @if(!empty($enquiry['guests']) && $enquiry['inquiry_type'] === 'villa_rental')
                    <div class="info-item" style="background-color: white;">
                        <div class="info-label">Number of Guests</div>
                        <div class="info-value">{{ $enquiry['guests'] }} {{ $enquiry['guests'] == 1 ? 'guest' : 'guests' }}</div>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <div class="message-section">
                <h3>📝 Message</h3>
                <div class="message-content">{{ $enquiry['message'] }}</div>
            </div>
        </div>

        <div class="footer">
            <p><strong>Bali Villa Spot</strong> - Contact Enquiry System</p>
            <p>This enquiry was submitted through the contact form on {{ config('app.url') }}</p>
            @if(in_array($enquiry['inquiry_type'], ['villa_rental', 'property_sale']))
                <p style="color: #dc2626; font-weight: 600;">⚡ High Priority - Business Enquiry</p>
            @endif
        </div>
    </div>
</body>
</html>