import { ApiService } from "./base";

export class AttachmentApi extends ApiService {
    static getAttachments(propertyId: number, options = {}) {
        return this.get(`/properties/${propertyId}/attachments`, options);
    }

    static createAttachment(propertyId: number, data: any, options = {}) {
        return this.post(`/properties/${propertyId}/attachments`, data, options);
    }

    static updateAttachment(propertyId: number, attachmentId: number, data: any, options = {}) {
        return this.put(`/properties/${propertyId}/attachments/${attachmentId}`, data, options);
    }

    static deleteAttachment(attachmentId: number, options = {}) {
        return this.delete(`/attachments/${attachmentId}`, options);
    }
}