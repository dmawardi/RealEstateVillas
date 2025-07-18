// resources/js/types/property.ts
export interface Property {
    id: number;
    title: string;
    description: string;
    property_type: string;
    listing_type: string;
    status: string;
    price?: number;
    rental_price_monthly?: number;
    rental_price_weekly?: number;
    street_number?: string;
    street_name: string;
    village?: string;
    district: string;
    regency: string;
    state: string;
    postcode: string;
    country: string;
    bedrooms?: number;
    bathrooms?: number;
    car_spaces?: number;
    land_size?: number;
    floor_area?: number;
    year_built?: number;
    attachments?: PropertyAttachment[];
    amenities?: Record<string, any>;
    features?: Feature[];
    user: User;
    agent_name: string;
    agent_phone?: string;
    agent_email?: string;
    agency_name?: string;
    view_count: number;
    created_at: string;
    updated_at: string;
}

export interface Feature {
    id: number;
    name: string;
    slug: string;
    category: string;
    pivot: {
        quantity: number;
        notes?: string;
    };
}

export interface PropertyAttachment {
    id: number;
    path: string;
    original_filename: string;
    file_type: string;
    file_size: number;
    type: string;
    caption?: string;
    is_visible_to_customer: boolean;
    is_active: boolean;
}

export interface User {
    id: number;
    name: string;
    email: string;
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

// Property-specific enums for better type safety
export type PropertyType = 'house' | 'apartment' | 'townhouse' | 'villa' | 'land' | 'commercial' | 'guest_house' | 'other';
export type ListingType = 'for_sale' | 'for_rent' | 'sold' | 'off_market';
export type PropertyStatus = 'active' | 'pending' | 'sold' | 'withdrawn';
export type PriceType = 'fixed' | 'negotiable' | 'auction' | 'poa';