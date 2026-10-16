/**
 * Thin wrapper over the Meta Pixel and Google tag snippets that the Blade
 * layout injects on the central marketing domain when their IDs are set.
 *
 * Both globals are optional: with no IDs configured (local, staging, tenant
 * sites) every call is a silent no-op, so components can track freely.
 */
type Fbq = (...args: unknown[]) => void;
type Gtag = (...args: unknown[]) => void;

declare global {
    interface Window {
        fbq?: Fbq;
        gtag?: Gtag;
        __courierosAds?: { googleLeadSendTo?: string | null };
    }
}

export type MarketingEvent =
    'Contact' | 'Lead' | 'InitiateCheckout' | 'ViewContent';

export function trackMarketing(
    event: MarketingEvent,
    params: Record<string, unknown> = {},
) {
    if (typeof window === 'undefined') {
        return;
    }

    window.fbq?.('track', event, params);

    const gaName: Record<MarketingEvent, string> = {
        Contact: 'contact',
        Lead: 'generate_lead',
        InitiateCheckout: 'begin_checkout',
        ViewContent: 'view_item',
    };

    window.gtag?.('event', gaName[event], params);

    // Google Ads conversion for the two actions that mean "a real lead".
    const sendTo = window.__courierosAds?.googleLeadSendTo;

    if (sendTo && (event === 'Contact' || event === 'Lead')) {
        window.gtag?.('event', 'conversion', { send_to: sendTo });
    }
}

/**
 * Builds a wa.me link with a pre-filled first message. The message names the
 * page and campaign so Shane can tell which ad a chat came from without
 * asking.
 */
export function whatsappLink(number: string, message: string): string {
    const digits = number.replace(/\D/g, '');

    return `https://wa.me/${digits}?text=${encodeURIComponent(message)}`;
}

/** The utm_campaign the visitor arrived with, if any, for chat attribution. */
export function currentCampaign(): string | null {
    if (typeof window === 'undefined') {
        return null;
    }

    return new URLSearchParams(window.location.search).get('utm_campaign');
}
