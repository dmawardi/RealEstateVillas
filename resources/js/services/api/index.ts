import { BookingApi } from './bookings';
import { PropertyApi } from './properties';
import { AttachmentApi } from './attachments';

// Convenience object for all APIs
export const api = {
    bookings: BookingApi,
    properties: PropertyApi,
    attachments: AttachmentApi
};