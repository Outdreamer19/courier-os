export type RateTier = {
    id?: number | null;
    name?: string;
    currency: string;
    rate_per_lb: number;
    minimum_charge: number;
    handling_fee?: number | null;
    min_weight_lbs?: number | null;
    max_weight_lbs?: number | null;
    tier_label?: string;
};

function tierMatches(weight: number, tier: RateTier): boolean {
    const min = tier.min_weight_lbs ?? 0;
    const max = tier.max_weight_lbs ?? Number.POSITIVE_INFINITY;

    return weight >= min && weight <= max;
}

export function estimateShipping(
    weight: number,
    tiers: RateTier[],
): { amount: number; tier: RateTier | null } {
    if (weight <= 0 || tiers.length === 0) {
        return { amount: 0, tier: null };
    }

    const tier = tiers.find((entry) => tierMatches(weight, entry)) ?? tiers[0];
    const handling = tier.handling_fee ?? 0;
    const amount = Math.max(tier.minimum_charge, weight * tier.rate_per_lb + handling);

    return { amount, tier };
}

export function formatMoney(currency: string, amount: number): string {
    return `${currency} $${amount.toLocaleString(undefined, {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    })}`;
}
