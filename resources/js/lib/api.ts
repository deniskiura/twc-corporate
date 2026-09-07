/**
 * A very small JSON client for our own API. The bearer token identifies the
 * caller the same way it would for any other client, so the admin screen has
 * no privileged path into the backend.
 */

export class ApiError extends Error {
    constructor(
        public readonly status: number,
        message: string,
        public readonly errors: Record<string, string[]> = {},
    ) {
        super(message);
    }

    /** The first validation message for a field, if the server sent one. */
    fieldError(field: string): string | undefined {
        return this.errors[field]?.[0];
    }
}

type Method = 'GET' | 'POST' | 'DELETE';

export function createApiClient(token: string) {
    async function request<T>(
        method: Method,
        url: string,
        body?: unknown,
    ): Promise<T> {
        const response = await fetch(url, {
            method,
            headers: {
                Accept: 'application/json',
                Authorization: `Bearer ${token}`,
                ...(body !== undefined
                    ? { 'Content-Type': 'application/json' }
                    : {}),
            },
            body: body !== undefined ? JSON.stringify(body) : undefined,
        });

        if (response.status === 204) {
            return undefined as T;
        }

        const payload = (await response.json().catch(() => ({}))) as {
            message?: string;
            errors?: Record<string, string[]>;
        };

        if (!response.ok) {
            throw new ApiError(
                response.status,
                payload.message ?? 'Something went wrong.',
                payload.errors ?? {},
            );
        }

        return payload as T;
    }

    return {
        get: <T>(url: string) => request<T>('GET', url),
        post: <T>(url: string, body?: unknown) => request<T>('POST', url, body),
        delete: <T>(url: string) => request<T>('DELETE', url),
    };
}
