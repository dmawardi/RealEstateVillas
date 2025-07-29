import { router } from '@inertiajs/vue3';

interface ApiOptions {
    onSuccess?: (data?: any) => void;
    onError?: (errors: Record<string, any>) => void;
    onFinish?: () => void;
}

/**
 * Provides a base API service for making HTTP requests using a router.
 * 
 * This class includes static methods for common HTTP operations (`get`, `post`, `put`, `delete`)
 * and error formatting. Each method accepts a URL and optional data or options, and handles
 * success, error, and finish callbacks.
 * 
 * @remarks
 * - The `formatErrors` method standardizes error responses into a consistent format.
 * - The `ApiOptions` type is expected to define optional `onSuccess`, `onError`, and `onFinish` callbacks.
 * - The `router` object is assumed to be available in the scope and provides HTTP methods.
 * 
 * @example
 * ```typescript
 * ApiService.post('/api/resource', { name: 'Example' }, {
 *   onSuccess: (response) => { ... },
 *   onError: (errors) => { ... },
 *   onFinish: () => { ... }
 * });
 * ```
 */
export class ApiService {
    protected static formatErrors(responseErrors: Record<string, any>): Record<string, string[]> {
        const formattedErrors: Record<string, string[]> = {};
        for (const [key, value] of Object.entries(responseErrors)) {
            formattedErrors[key] = Array.isArray(value) ? value : [String(value)];
        }
        return formattedErrors;
    }

    protected static post(url: string, data: any, options: ApiOptions = {}) {
        return router.post(url, data, {
            onSuccess: options.onSuccess,
            onError: (responseErrors) => {
                if (options.onError) {
                    options.onError(this.formatErrors(responseErrors));
                }
            },
            onFinish: options.onFinish
        });
    }

    protected static get(url: string, options: ApiOptions = {}) {
        return router.get(url, undefined, {
            onSuccess: options.onSuccess,
            onError: (responseErrors) => {
                if (options.onError) {
                    options.onError(this.formatErrors(responseErrors));
                }
            },
            onFinish: options.onFinish
        });
    }

    protected static put(url: string, data: any, options: ApiOptions = {}) {
        return router.put(url, data, {
            onSuccess: options.onSuccess,
            onError: (responseErrors) => {
                if (options.onError) {
                    options.onError(this.formatErrors(responseErrors));
                }
            },
            onFinish: options.onFinish
        });
    }

    protected static delete(url: string, options: ApiOptions = {}) {
        return router.delete(url, {
            onSuccess: options.onSuccess,
            onError: (responseErrors) => {
                if (options.onError) {
                    options.onError(this.formatErrors(responseErrors));
                }
            },
            onFinish: options.onFinish
        });
    }
}