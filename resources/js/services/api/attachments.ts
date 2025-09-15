import { ApiService, ApiOptions } from './base';

export class AttachmentApi extends ApiService {
    // Get all attachments for a property
    static getAttachments(propertyId: number, options: ApiOptions = {}) {
        return this.get(`/properties/${propertyId}/attachments`, options);
    }

    // Create/upload new attachments for a property
    static createAttachment(propertyId: number, data: FormData, options: ApiOptions = {}) {
        // Remove the headers override - let base.ts handle FormData automatically
        return this.post(`/properties/${propertyId}/attachments`, data, options);
    }

    // Update an existing attachment
    static updateAttachment(attachmentId: number, data: any, options: ApiOptions = {}) {
        return this.put(`/attachments/${attachmentId}`, data, options);
    }

    // Delete an attachment
    static deleteAttachment(attachmentId: number, options: ApiOptions = {}) {
        return this.delete(`/attachments/${attachmentId}`, options);
    }

    // Toggle attachment visibility
    static toggleVisibility(attachmentId: number, options: ApiOptions = {}) {
        return this.patch(`/attachments/${attachmentId}/toggle-visibility`, {}, options);
    }

    // Update attachment order for a property
    static updateOrder(propertyId: number, attachments: Array<{id: number, order: number}>, options: ApiOptions = {}) {
        return this.put(`/properties/${propertyId}/attachments/order`, { attachments }, options);
    }

    // Get a specific attachment
    static getAttachment(attachmentId: number, options: ApiOptions = {}) {
        return this.get(`/attachments/${attachmentId}`, options);
    }
}