export interface ApiOptions {
    onSuccess?: (response: any) => void;
    onError?: (errors: any) => void;
    onFinish?: () => void;
}

export class ApiService {
    private static async makeRequest(
        method: string, 
        url: string, 
        data?: any, 
        options: ApiOptions = {}
    ) {
        const { onSuccess, onError, onFinish } = options;

        try {
            // Determine if this is a FormData request
            const isFormData = data instanceof FormData;            
            
            const headers: Record<string, string> = {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            };

            // Get CSRF token from meta tag
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (csrfToken) {
                headers['X-CSRF-TOKEN'] = csrfToken;
            }

            // Only set Content-Type for non-FormData requests
            // For FormData, let the browser set the Content-Type with boundary
            if (!isFormData) {
                headers['Content-Type'] = 'application/json';
            }

            const response = await fetch('/api' + url, {
                method,
                headers,
                // For FormData, send directly; for others, stringify
                body: data ? (isFormData ? data : JSON.stringify(data)) : undefined,
            });

            if (!response.ok) {
                const errorData = await response.json().catch(() => ({ message: 'Request failed' }));
                if (onError) onError(errorData);
                return;
            }

            const responseData = await response.json();
            if (onSuccess) onSuccess(responseData);

            return responseData;
            
        } catch (error) {
            console.error('API request failed:', error);
            if (onError) onError({ message: 'Network error occurred' });
        } finally {
            if (onFinish) onFinish();
        }
    }

    static get(url: string, options: ApiOptions = {}) {
        return this.makeRequest('GET', url, undefined, options);
    }

    static post(url: string, data: any, options: ApiOptions = {}) {
        return this.makeRequest('POST', url, data, options);
    }

    static put(url: string, data: any, options: ApiOptions = {}) {
        return this.makeRequest('PUT', url, data, options);
    }

    static delete(url: string, options: ApiOptions = {}) {
        return this.makeRequest('DELETE', url, undefined, options);
    }

    static patch(url: string, data: any, options: ApiOptions = {}) {
        return this.makeRequest('PATCH', url, data, options);
    }
}