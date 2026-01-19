export interface Property {
    id: number;
    
    // Basic Property Information
    title: string;
    slug: string;
    description: string;
    property_type: PropertyType;
    listing_type: ListingType;
    status: PropertyStatus;
    
    // Pricing - Updated to match migration
    price?: number; // integer in migration
    price_type: PriceType;
    
    // Address Information - Bali specific structure
    street_number?: string;
    street_name: string;
    village?: string; // Desa/Kelurahan
    district: string; // Kecamatan
    regency: string; // Kabupaten/Kota
    state: string; // Provinsi (default: 'Bali')
    postcode: string;
    country: string; // default: 'Indonesia'
    latitude?: number; // decimal(10,8)
    longitude?: number; // decimal(11,8)
    
    // Property Specifications
    bedrooms?: number;
    bathrooms?: number;
    car_spaces?: number;
    land_size?: number; // decimal(10,2) in square meters
    floor_area?: number; // decimal(10,2) in square meters
    year_built?: number;
    
    // Amenities & Details
    amenities?: Record<string, any>; // JSON field
    zoning?: string;
    is_featured: boolean;
    is_premium: boolean;
    available_date?: string; // date
    inspection_times?: string; // text field
    always_override_availability?: boolean; // For hotels with multiple rooms
    only_monthly_allowed?: boolean; // For long-term rentals

    is_favorited?: boolean; // Added to track if the property is favorited by the current user
    
    // Media
    floor_plan?: string; // path to floor plan
    virtual_tour_url?: string;
    video_url?: string;

    // Bookings
    bookings?: Booking[]; // hasMany relationship
    
    // Agent Information
    user_id: number;
    agent_name: string;
    agent_phone?: string;
    agent_email?: string;
    agency_name?: string;
    
    // Listing Management
    property_id: string; // unique custom property reference
    listed_at?: string; // timestamp
    days_on_market: number; // integer, default 0
    view_count: number; // integer, default 0
    
    // Relationships
    user: User;
    attachments?: PropertyAttachment[];
    features?: Feature[];
    pricing?: PropertyPricing[]; // hasMany relationship
    
    // Timestamps
    created_at: string;
    updated_at: string;

    // Helper
    getCurrentPricing?: () => string;
    pricing_string?:string;
}

export interface Booking {
    id: number;
    check_in_date: string;
    check_out_date: string;
    status: string;
    booking_type: string;
    source: string;
    first_name: string;
    last_name?: string;
    email: string;
    phone?: string;
    number_of_guests: number;
    total_price?: number;
    special_requests?: string;
    notes?: string;
    external_booking_id?: string;
    commission_amount?: number;
    commission_paid?: boolean;
    commission_rate?: number;
    number_of_rooms?: number;
    flexible_dates?: boolean;
    created_at: string;
    updated_at: string;
    property: Property;
    // Relationships
    user: User;
}

export interface Feature {
    id: number;
    name: string;
    slug: string;
    description?: string;
    icon?: string;
    is_quantifiable: boolean;
    is_active: boolean;
    category: string;
    pivot: {
        quantity: number;
        notes?: string;
    };
}

export interface DetailedPricing {
    nightly: {
        rate: number;
        display: string;
    };
    weekly: {
        rate: number;
        display: string;
        discount: number;
        hasDiscount: boolean;
    };
    monthly: {
        rate: number;
        display: string;
        discount: number;
        hasDiscount: boolean;
    };
    periodName: string | undefined;
}

export interface PropertyAttachment {
    id: number;
    title: string;
    path: string;
    original_filename: string;
    file_type: string;
    file_size: number;
    type: string;
    caption?: string;
    is_visible_to_customer: boolean;
    is_active: boolean;
    order: number;
    updated_at: string;
    created_at: string;
    url: string;
}

export interface PaginatedProperties {
    data: Property[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
    links: {
        url: string | null;
        label: string;
        active: boolean;
    }[];
}

export interface PaginatedFeatures {
    data: Feature[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
}

export interface paginatedBookings {
    data: Booking[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
    links: {
        url: string | null;
        label: string;
        active: boolean;
    }[];
}

export interface PropertyFilters {
    property_type?: string;
    listing_type?: string;
    bedrooms?: string;
    village?: string;
    check_in_date: string;
    check_out_date: string;
}

export interface Availability {
    period_start: string;
    period_end: string;
    unavailable_periods: UnavailablePeriod[];
}

export interface UnavailablePeriod {
    start: string;
    end: string;
    type: 'booked' | 'blocked' | 'maintenance';
}

export interface PropertyPricing {
    id: number;
    property_id: number;
    name?: string;
    nightly_rate: number;
    weekly_discount_percent?: number;
    monthly_discount_percent?: number;
    weekend_premium_percent?: number;
    weekly_discount_active?: boolean;
    monthly_discount_active?: boolean;
    weekend_premium_active?: boolean;
    min_days_for_weekly?: number;
    min_days_for_monthly?: number;
    currency: string;
    start_date?: string;
    end_date?: string;
    created_at?: string;
    updated_at?: string;
}

export interface DateRange {
    checkIn: string;
    checkOut: string;
}

export interface Location {
    name: string;
    type: 'regency' | 'district' | 'village';
    parent?: string; // For hierarchy display
}

// Property-specific enums for better type safety
export type PropertyType = 'house' | 'apartment' | 'townhouse' | 'villa' | 'land' | 'commercial' | 'guest_house' | 'other';
export type ListingType = 'for_sale' | 'for_rent' | 'sold' | 'off_market';
export type PropertyStatus = 'active' | 'pending' | 'sold' | 'withdrawn';
export type PriceType = 'fixed' | 'negotiable' | 'auction' | 'poa';