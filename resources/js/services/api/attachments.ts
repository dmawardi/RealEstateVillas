import { ApiService } from "./base";

export class AttachmentApi extends ApiService {
    static getAttachments(propertyId: number) {
        return this.get(`/properties/${propertyId}/attachments`);
    }

    static createAttachment(propertyId: number, data: FormData) {
        return this.post(`/properties/${propertyId}/attachments`, data);
    }

    static updateAttachment(propertyId: number, attachmentId: number, data: FormData) {
        return this.put(`/properties/${propertyId}/attachments/${attachmentId}`, data);
    }

    static deleteAttachment(attachmentId: number) {
        return this.delete(`/attachments/${attachmentId}`);
    }
}