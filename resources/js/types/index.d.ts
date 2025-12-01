import type { LucideIcon } from 'lucide-vue-next';
import type { Config } from 'ziggy-js';
import { Booking, Property } from './property';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon;
    isActive?: boolean;
}

export interface SEO {
    title: string;
    description: string;
    keywords: string;
    canonicalUrl: string;
    ogImage?: string;
}

export type AppPageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    ziggy: Config & { location: string };
    sidebarOpen: boolean;
};

export interface User {
    id: number;
    name: string;
    email: string;
    role: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    properties: Property[];
    bookings: Booking[];
}

export interface PaginatedUsers {
    data: User[];
    current_page: number;
    last_page: number;
    from: number;
    to: number;
    total: number;
    links: Array<{
        url: string | null;
        label: string;
        active: boolean;
    }>;
}

export type BreadcrumbItemType = BreadcrumbItem;

// Re-export property types for convenience
export * from './property';