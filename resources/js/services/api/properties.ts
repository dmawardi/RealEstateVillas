import { ApiService } from './base';

export class PropertyApi extends ApiService {
    static getAllProperties(options = {}) {
        return this.get('/properties', options);
    }
    static getPropertyById(id: number, options = {}) {
        return this.get(`/properties/${id}`, options);
    }
    static getPropertyAvailability(id: number, options = {}) {
        return this.get(`/properties/${id}/availability`, options);
    }

   
    // Admin
    static createProperty(data: any, options = {}) {
        return this.post('/properties', data, options);
    }
    static updateProperty(id: number, data: any, options = {}) {
        return this.put(`/properties/${id}`, data, options);
    }
    static deleteProperty(id: number, options = {}) {
        return this.delete(`/properties/${id}`, options);
    }
}