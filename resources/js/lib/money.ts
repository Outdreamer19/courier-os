import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import type { BrandConfig } from '@/types/auth'

/**
 * Format a numeric amount into a human-readable string with the given
 * currency code prefix.
 *
 * e.g. formatMoney(1250.5, 'JMD') → "JMD $1,250.50"
 */
export function formatMoney(amount: number, currency: string): string {
    return `${currency} $${amount.toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    })}`
}

/**
 * Composable that reads the active tenant's currency from the shared Inertia
 * props and returns a pre-bound `format(amount)` helper.
 *
 * Usage inside a component:
 *   const { currency, format } = useMoney()
 *   format(package.amount_due)  // "USD $12.50"
 */
export function useMoney() {
    const page = usePage()
    const currency = computed(() => (page.props.brand as BrandConfig | undefined)?.currency ?? 'USD')

    const format = (amount: number): string => formatMoney(amount, currency.value)

    return { currency, format }
}
