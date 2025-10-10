import { InertiaLinkProps } from '@inertiajs/vue3';
import type { LucideIcon } from 'lucide-vue-next';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon;
    isActive?: boolean;
}

export type AppPageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    sidebarOpen: boolean;
    success?: string;
    error?: string;
    info?: string;
    warning?: string;
    status?: string;
    errors?: Record<string, string[]>;
};

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
}

export type Pagination = {
    firstPage: number;
    currentPage: number;
    lastPage: number;
    firstPageUrl: string | null;
    lastPageUrl: string | null;
    perPage: number;
    nextPageUrl: string | null;
    prevPageUrl: string | null;
    total: number;
    hasMorePages: boolean;
};

export type PaginatedData<T> = {
    items: T[];
    pagination: Pagination;
};

export type BreadcrumbItemType = BreadcrumbItem;
