<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AnimatedBarChart from '@/components/marketing/AnimatedBarChart.vue';
import AnimatedStatPanel from '@/components/marketing/AnimatedStatPanel.vue';
import AnnouncementBar from '@/components/marketing/AnnouncementBar.vue';
import FaqAccordion from '@/components/marketing/FaqAccordion.vue';
import FeatureGrid from '@/components/marketing/FeatureGrid.vue';
import FeatureShowcase from '@/components/marketing/FeatureShowcase.vue';
import FinalCta from '@/components/marketing/FinalCta.vue';
import HeroSection from '@/components/marketing/HeroSection.vue';
import IntegrationsGrid from '@/components/marketing/IntegrationsGrid.vue';
import LogoMarquee from '@/components/marketing/LogoMarquee.vue';
import MarketingFooter from '@/components/marketing/MarketingFooter.vue';
import MarketingNav from '@/components/marketing/MarketingNav.vue';
import PricingSection from '@/components/marketing/PricingSection.vue';
import StatsBanner from '@/components/marketing/StatsBanner.vue';
import TestimonialSwitcher from '@/components/marketing/TestimonialSwitcher.vue';
import { useScrollReveal } from '@/composables/useScrollReveal';

defineProps<{
    pricing: { monthly: number; setup: number; currency: string };
}>();

useScrollReveal();
</script>

<template>
    <Head title="CourierOS — Run Your Courier Business Like a Platform" />

    <div class="bg-marketing-cream font-marketing text-marketing-ink">
        <AnnouncementBar
            text="Now onboarding Caribbean courier businesses — start free"
            href="/signup"
        />
        <MarketingNav />

        <HeroSection />

        <LogoMarquee :logos="[]" />

        <TestimonialSwitcher :testimonials="[]" />

        <IntegrationsGrid />

        <div class="mx-auto max-w-6xl space-y-8 px-4 py-4 sm:px-6 lg:px-8">
            <FeatureShowcase
                eyebrow="What CourierOS can do for you"
                title="Real-time package &amp; pre-alert tracking"
                body="Every pre-alert and package status update lands in one dashboard, so you always know what's moving through your warehouse."
                :bullets="[
                    'Live status timeline',
                    'Instant pre-alert matching',
                ]"
            >
                <template #visual>
                    <AnimatedStatPanel
                        title="Package Status Overview"
                        :percent="72"
                        percent-label="of packages currently in transit"
                        :rows="[
                            {
                                label: 'Pre-Alerted',
                                percent: 88,
                                color: '#FA8F1F',
                            },
                            {
                                label: 'At Warehouse',
                                percent: 64,
                                color: '#FCD519',
                            },
                            {
                                label: 'Ready for Pickup',
                                percent: 41,
                                color: '#AA8322',
                            },
                        ]"
                    />
                </template>
            </FeatureShowcase>

            <FeatureShowcase
                reverse
                eyebrow="Grow with confidence"
                title="Clear performance metrics for your business"
                body="Track packages processed and revenue trends so you know your business is growing — not just guessing."
                :bullets="[
                    'Monthly package volume',
                    'Revenue trend at a glance',
                ]"
            >
                <template #visual>
                    <AnimatedBarChart
                        title="Packages Processed"
                        :bars="[
                            { label: 'Jan', percent: 40, color: '#FA8F1F' },
                            { label: 'Feb', percent: 55, color: '#FA8F1F' },
                            { label: 'Mar', percent: 70, color: '#FA8F1F' },
                            { label: 'Apr', percent: 85, color: '#FCD519' },
                        ]"
                    />
                </template>
            </FeatureShowcase>

            <FeatureShowcase
                eyebrow="Ditch the spreadsheet"
                title="See how CourierOS compares to manual tracking"
                body="Spreadsheets and group chats don't scale. CourierOS replaces manual package tracking with a system built for it."
                :bullets="[
                    'No more lost pre-alerts',
                    'No more manual status updates',
                ]"
            >
                <template #visual>
                    <AnimatedBarChart
                        title="Time Spent on Package Admin"
                        :bars="[
                            {
                                label: 'Spreadsheets & DMs',
                                percent: 100,
                                color: '#AA8322',
                            },
                            {
                                label: 'CourierOS',
                                percent: 35,
                                color: '#FA8F1F',
                                note: '65% less',
                            },
                        ]"
                    />
                </template>
            </FeatureShowcase>
        </div>

        <StatsBanner />

        <PricingSection :pricing="pricing" />

        <FeatureGrid />

        <FaqAccordion />

        <FinalCta />

        <MarketingFooter />
    </div>
</template>
