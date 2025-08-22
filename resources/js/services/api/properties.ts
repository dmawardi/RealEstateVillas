import { ApiService } from './base';

export class PropertyApi extends ApiService {
    static getAllProperties(filters = {}, options = {}) {
        // Build query string from filters
        const queryParams = new URLSearchParams();
        Object.entries(filters).forEach(([key, value]) => {
            if (value) {
                queryParams.append(key, String(value));
            }
        });
        
        const url = queryParams.toString() 
            ? `/properties?${queryParams.toString()}` 
            : '/properties';
            
        return this.get(url, options);
    }

    static getPropertyById(id: number, options = {}) {
        return this.get(`/properties/${id}`, options);
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

    // API routes (returns JSON)
    static getAvailability(id: number, params: { start: string; end: string }, options = {}) {
        const queryParams = new URLSearchParams({
            start: params.start,
            end: params.end
        });
        
        return this.get(`/api/properties/${id}/availability?${queryParams.toString()}`, options);
    }
    static getAllLocations(options = {}) {
        return this.get('/api/properties/locations', options);
    }
}