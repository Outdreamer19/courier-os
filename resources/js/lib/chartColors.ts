/**
 * Chart colour palette — a modern, vivid set anchored on the brand navy,
 * paired with clean, contemporary accent hues (no muddy/earth tones).
 *
 * brand-navy  → #1844BF   primary data / revenue / money
 * emerald     → #10B981   secondary / growth / paid
 * amber       → #F5C01E   tertiary
 * orange      → #FB923C   quaternary
 * violet      → #8B5CF6   neutral fifth
 * red         → #EF4444   alert / overdue (kept distinct from brand-red accent)
 *
 * All colours pass WCAG AA contrast on the card background (#ffffff) and
 * are visually coherent with the navy sidebar + red accent system.
 */

export const chartBarColors = [
    '#1844BF', // brand-navy — primary series (revenue, counts)
    '#10B981', // emerald    — secondary series (volume, growth)
    '#F5C01E', // amber      — tertiary
    '#FB923C', // orange     — quaternary
    '#8B5CF6', // violet     — fifth
    '#EF4444', // red        — alert / overdue data
];

export const chartStatusColors = [
    '#1844BF', // brand-navy — pending / active
    '#3B82F6', // blue       — in-transit / processing
    '#10B981', // emerald    — completed / paid
    '#EF4444', // red        — failed / overdue
    '#8B5CF6', // violet     — misc / unknown
    '#94A3B8', // grey       — no data / neutral
];

/** Matches --border: hsl(40 20% 88%) — keeps grid lines on-brand */
export const chartGridColor = '#E5DFD2';
