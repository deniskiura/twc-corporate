export type Plan = {
    id: number;
    name: string;
    monthly_credits: number;
    monthly_price: number;
    currency: string;
};

export type SeatStatus = 'invited' | 'joined' | 'suspended';

export type CreditSummary = {
    allowance: number;
    used: number;
    remaining: number;
    exhausted: boolean;
};

/** One managed seat as the company admin sees it. */
export type SponsoredUser = {
    id: number;
    email: string;
    name: string | null;
    status: SeatStatus;
    plan: Plan;
    invited_at: string;
    last_sent_at: string;
    joined_at: string | null;
    suspended_at: string | null;
    days_pending: number | null;
    is_stale: boolean;
    credits: CreditSummary;
};

/** A seat straight after its invite was created or resent, with the link. */
export type Invitation = SponsoredUser & {
    invite_token: string;
    invite_url: string;
};

export type BillingCycle = {
    start: string;
    end: string;
    bills_on: string;
};

export type TeamTotals = {
    joined: number;
    suspended: number;
    invited: number;
    stale_invites: number;
    credits: {
        allowance: number;
        used: number;
        exhausted: boolean;
    };
};

export type TeamResponse = {
    data: SponsoredUser[];
    meta: {
        company: { id: number; name: string };
        billing_cycle: BillingCycle;
        totals: TeamTotals;
    };
};

/** What a sponsored employee sees on their dashboard. */
export type Membership = {
    status: 'joined' | 'suspended';
    company: string;
    plan: Plan;
    joined_at: string;
    billing_cycle: BillingCycle;
    credits: CreditSummary & { purchased: number };
};
