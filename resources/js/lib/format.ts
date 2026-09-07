const shortDate = new Intl.DateTimeFormat('en-KE', {
    day: 'numeric',
    month: 'short',
});

const longDate = new Intl.DateTimeFormat('en-KE', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
});

/** "7 Sep" */
export function formatDate(iso: string): string {
    return shortDate.format(new Date(iso));
}

/** "30 September 2026" */
export function formatLongDate(iso: string): string {
    return longDate.format(new Date(iso));
}

/** "KES 7,500" */
export function formatMoney(amount: number, currency: string): string {
    return `${currency} ${amount.toLocaleString('en-KE')}`;
}

/** "today", "yesterday", "23 days ago" */
export function daysAgo(days: number): string {
    if (days === 0) {
        return 'today';
    }

    if (days === 1) {
        return 'yesterday';
    }

    return `${days} days ago`;
}

/** The first day after a billing cycle ends, when allowances reset. */
export function resetDate(cycleEnd: string): string {
    const next = new Date(cycleEnd);
    next.setDate(next.getDate() + 1);

    return longDate.format(next);
}
