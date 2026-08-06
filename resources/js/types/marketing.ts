/**
 * Shared shapes for the marketing site components.
 *
 * These live here rather than in the components themselves because a
 * `<script setup>` block cannot have named exports, and the pages that supply
 * this content need the types to stay honest about it.
 */

/** One tab in the interactive product tour on /product. */
export type TourStop = {
    /** Stable slug — used to build the tab/panel `aria` ids. */
    id: string;
    /** Short tab label. */
    label: string;
    /** Headline shown beside the screenshot. */
    title: string;
    body: string;
    bullets: string[];
    /** PNG fallback path, served from /public. */
    image: string;
    /** Optional WebP alongside `image`, preferred when supported. */
    webp?: string;
    alt: string;
};

/** One numbered step in the end-to-end workflow walkthrough. */
export type WorkflowStep = {
    /** Who performs the step — "Your customer", "Your team", and so on. */
    actor: string;
    title: string;
    body: string;
};
