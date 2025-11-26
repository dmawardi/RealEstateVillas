const statusLabels: Record<string, string> = {
    pending: 'Pending',
    confirmed: 'Confirmed',
    cancelled: 'Cancelled',
    completed: 'Completed',
    blocked: 'Blocked',
    withdrawn: 'Withdrawn',
};

const sourceLabels: Record<string, string> = {
    direct: 'Direct',
    airbnb: 'Airbnb',
    booking_com: 'Booking.com',
    agoda: 'Agoda',
    owner_blocked: 'Owner Blocked',
    maintenance: 'Maintenance',
    other: 'Other',
};

const bookingTypeLabels: Record<string, string> = {
    booking: 'Booking',
    inquiry: 'Inquiry',
    blocked: 'Blocked',
    maintenance: 'Maintenance',
};

const propertyTypeLabels: Record<string, string> = {
    apartment: 'Apartment',
    villa: 'Villa',
    land: 'Land',
    house: 'House',
    guest_house: 'Guest House',
    townhouse: 'Townhouse',
    commercial: 'Commercial',
};

export { statusLabels, sourceLabels, bookingTypeLabels, propertyTypeLabels };