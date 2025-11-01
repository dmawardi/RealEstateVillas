import { ApiService } from './base';

export class BookingApi extends ApiService {
    static createBooking(propertyId: number, data: any, options = {}) {
        return this.post(`/properties/${propertyId}/bookings`, data, options);
    }
    static updateBooking(bookingId: number, data: any, options = {}) {
        return this.put(`/bookings/${bookingId}`, data, options);
    }
    static getBookings(propertyId: number, options = {}) {
        return this.get(`/properties/${propertyId}/bookings`, options);
    }
    static getBooking(propertyId: number, bookingId: number, options = {}) {
        return this.get(`/properties/${propertyId}/bookings/${bookingId}`, options);
    }
    static deleteBooking(bookingId: number, options = {}) {
        return this.delete(`/bookings/${bookingId}`, options);
    }
    static withdrawBooking(bookingId: number, data: any, options = {}) {
        return this.post(`/bookings/${bookingId}/withdraw`, data, options);
    }
}