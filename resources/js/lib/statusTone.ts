/**
 * Shared colour vocabulary for lifecycle and payment states.
 *
 * Both the pill badge and the quieter inline treatments (a dot plus coloured
 * label, e.g. the payment caption under an amount) read from the same map, so
 * a "paid" package is the same green wherever it appears.
 */
export type StatusTone = {
    /** Fill + border + text for a pill badge. */
    badge: string;
    /** Saturated dot, used by both the badge and the inline caption. */
    dot: string;
    /** Text colour only, for inline captions that carry no fill. */
    text: string;
};

/**
 * The raw palette. Fills sit at ~15% so several of these can share a table
 * without shouting; borders and dots stay fully saturated so each tone still
 * has a crisp edge.
 */
export const STATUS_TONES = {
    slate: {
        badge: 'border-slate-400/50 bg-slate-400/15 text-slate-600 dark:border-slate-400/50 dark:bg-slate-400/15 dark:text-slate-300',
        dot: 'bg-slate-400 dark:bg-slate-400',
        text: 'text-slate-600 dark:text-slate-400',
    },
    indigo: {
        badge: 'border-indigo-500/45 bg-indigo-500/15 text-indigo-700 dark:border-indigo-400/50 dark:bg-indigo-400/15 dark:text-indigo-200',
        dot: 'bg-indigo-500 dark:bg-indigo-400',
        text: 'text-indigo-700 dark:text-indigo-400',
    },
    violet: {
        badge: 'border-violet-500/45 bg-violet-500/15 text-violet-700 dark:border-violet-400/50 dark:bg-violet-400/15 dark:text-violet-200',
        dot: 'bg-violet-500 dark:bg-violet-400',
        text: 'text-violet-700 dark:text-violet-400',
    },
    sky: {
        badge: 'border-sky-500/45 bg-sky-500/15 text-sky-700 dark:border-sky-400/50 dark:bg-sky-400/15 dark:text-sky-200',
        dot: 'bg-sky-500 dark:bg-sky-400',
        text: 'text-sky-700 dark:text-sky-400',
    },
    cyan: {
        badge: 'border-cyan-500/45 bg-cyan-500/15 text-cyan-700 dark:border-cyan-400/50 dark:bg-cyan-400/15 dark:text-cyan-200',
        dot: 'bg-cyan-500 dark:bg-cyan-400',
        text: 'text-cyan-700 dark:text-cyan-400',
    },
    amber: {
        badge: 'border-amber-500/50 bg-amber-500/20 text-amber-800 dark:border-amber-400/50 dark:bg-amber-400/15 dark:text-amber-200',
        dot: 'bg-amber-500 dark:bg-amber-400',
        text: 'text-amber-700 dark:text-amber-400',
    },
    orange: {
        badge: 'border-orange-500/50 bg-orange-500/15 text-orange-700 dark:border-orange-400/50 dark:bg-orange-400/15 dark:text-orange-200',
        dot: 'bg-orange-500 dark:bg-orange-400',
        text: 'text-orange-700 dark:text-orange-400',
    },
    teal: {
        badge: 'border-teal-500/45 bg-teal-500/15 text-teal-700 dark:border-teal-400/50 dark:bg-teal-400/15 dark:text-teal-200',
        dot: 'bg-teal-500 dark:bg-teal-400',
        text: 'text-teal-700 dark:text-teal-400',
    },
    emerald: {
        badge: 'border-emerald-500/45 bg-emerald-500/15 text-emerald-700 dark:border-emerald-400/50 dark:bg-emerald-400/15 dark:text-emerald-200',
        dot: 'bg-emerald-500 dark:bg-emerald-400',
        text: 'text-emerald-700 dark:text-emerald-400',
    },
    rose: {
        badge: 'border-rose-500/45 bg-rose-500/15 text-rose-700 dark:border-rose-400/50 dark:bg-rose-400/15 dark:text-rose-200',
        dot: 'bg-rose-500 dark:bg-rose-400',
        text: 'text-rose-600 dark:text-rose-400',
    },
    neutral: {
        badge: 'border-border bg-muted text-muted-foreground',
        dot: 'bg-muted-foreground/70',
        text: 'text-muted-foreground',
    },
} satisfies Record<string, StatusTone>;

type ToneName = keyof typeof STATUS_TONES;

/**
 * Exact matches for the states we actually ship, so a package's journey reads
 * as a progression rather than three shades of blue: pale slate before it
 * exists, indigo → violet → sky → cyan as it moves, amber at customs, emerald
 * when the customer can collect, and a quiet neutral once it's gone. Problem
 * states break the sequence with orange and red.
 */
const TONE_BY_STATUS: Record<string, ToneName> = {
    // Package lifecycle
    awaiting_arrival: 'slate',
    received_at_florida_warehouse: 'indigo',
    processing: 'violet',
    in_transit_to_jamaica: 'sky',
    arrived_in_jamaica: 'cyan',
    customs_processing: 'amber',
    // Emerald is reserved for the one state that needs someone to act; the
    // finished state stays in the green family but steps aside into teal.
    ready_for_pickup: 'emerald',
    picked_up: 'teal',
    on_hold: 'orange',
    cancelled: 'rose',

    // Payment
    unpaid: 'rose',
    pending: 'amber',
    paid: 'emerald',
    waived: 'slate',
    refunded: 'violet',

    // Pre-alerts
    submitted: 'sky',
    under_review: 'amber',
    matched: 'emerald',
    expired: 'neutral',

    // Contact inbox
    new: 'amber',
    read: 'sky',
    responded: 'emerald',
    archived: 'neutral',
};

/**
 * Map a raw status/payment enum value onto a tone.
 *
 * Exact matches win; anything unrecognised falls through to keyword matching so
 * new enum cases still get a sensible colour instead of going grey. Negative
 * keywords are checked first on purpose: "unpaid" contains "paid", so a
 * positive-first check would quietly paint an unpaid package green.
 */
export function statusTone(status: string): StatusTone {
    const value = status.toLowerCase();
    const exact = TONE_BY_STATUS[value];

    if (exact) {
        return STATUS_TONES[exact];
    }

    if (
        value.includes('issue') ||
        value.includes('cancel') ||
        value.includes('unpaid') ||
        value.includes('failed') ||
        value.includes('overdue') ||
        value.includes('reject')
    ) {
        return STATUS_TONES.rose;
    }

    if (value.includes('hold') || value.includes('block')) {
        return STATUS_TONES.orange;
    }

    if (
        value.includes('paid') ||
        value.includes('completed') ||
        value.includes('ready') ||
        value.includes('delivered') ||
        value.includes('approved') ||
        value.includes('active')
    ) {
        return STATUS_TONES.emerald;
    }

    if (
        value.includes('review') ||
        value.includes('pending') ||
        value.includes('awaiting') ||
        value.includes('partial')
    ) {
        return STATUS_TONES.amber;
    }

    if (value.includes('processing') || value.includes('draft')) {
        return STATUS_TONES.violet;
    }

    if (
        value.includes('transit') ||
        value.includes('shipped') ||
        value.includes('sent')
    ) {
        return STATUS_TONES.sky;
    }

    if (value.includes('arrived') || value.includes('received')) {
        return STATUS_TONES.cyan;
    }

    if (value.includes('warehouse') || value.includes('customs')) {
        return STATUS_TONES.indigo;
    }

    return STATUS_TONES.neutral;
}
