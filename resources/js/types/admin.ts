import type { UserRole } from './auth';
import type { Plan } from './corporate';

/** Laravel's length-aware paginator, as Inertia serialises it. */
export type Paginated<T> = {
    data: T[];
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
    prev_page_url: string | null;
    next_page_url: string | null;
};

export type AdminStats = {
    companies: number;
    users: number;
    joined_seats: number;
    pending_invites: number;
    stale_invites: number;
    active_subscriptions: number;
    monthly_run_rate: number;
    credits: {
        allowance: number;
        used: number;
    };
};

export type CompanyAdmin = {
    id: number;
    name: string;
    email: string;
};

export type CompanySummary = {
    id: number;
    name: string;
    created_at: string;
    admins: CompanyAdmin[];
    joined_count: number;
    invited_count: number;
    stale_invites_count: number;
    monthly_run_rate: number;
};

export type SubscriptionRow = {
    id: number;
    employee: { name: string | null; email: string };
    company: { id: number; name: string };
    plan: Plan;
    started_at: string;
    ended_at: string | null;
    is_active: boolean;
};

export type UserRow = {
    id: number;
    name: string;
    email: string;
    role: UserRole;
    company: { id: number; name: string } | null;
    created_at: string;
};
