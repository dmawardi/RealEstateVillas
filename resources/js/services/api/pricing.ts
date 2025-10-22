import { ApiService } from './base';

export class PricingApi extends ApiService {
    static getPricing(propertyId: number, options = {}) {
        return this.get(`/properties/${propertyId}/pricing`, options);
    }
    
    static createPricing(propertyId: number, data: any, options = {}) {
        return this.post(`/properties/${propertyId}/pricing`, data, options);
    }
    
    static updatePricing(pricingId: number, data: any, options = {}) {
        return this.put(`/pricing/${pricingId}`, data, options);
    }
    
    static deletePricing(pricingId: number, options = {}) {
        return this.delete(`/pricing/${pricingId}`, options);
    }
}