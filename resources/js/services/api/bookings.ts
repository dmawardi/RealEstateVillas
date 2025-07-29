import { ApiService } from './base';

export class BookingApi extends ApiService {
    static createBooking(propertyId: number, data: any, options = {}) {
        return this.post(`/properties/${propertyId}/bookings`, data, options);
    }

    // Admin
    // static getAllBookings(propertyId?: number, options = {}) {
    //     const url = propertyId ? `/properties/${propertyId}/bookings` : '/bookings';
    //     return this.get(url, options);
    // }
    // static updateBooking(id: number, data: any, options = {}) {
    //     return this.put(`/bookings/${id}`, data, options);
    // }
    // static deleteBooking(id: number, options = {}) {
    //     return this.delete(`/bookings/${id}`, options);
    // }
}