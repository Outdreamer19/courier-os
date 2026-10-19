<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AnimatedBarChart from '@/components/marketing/AnimatedBarChart.vue';
import AnnouncementBar from '@/components/marketing/AnnouncementBar.vue';
import FaqAccordion from '@/components/marketing/FaqAccordion.vue';
import FeatureGrid from '@/components/marketing/FeatureGrid.vue';
import FeatureShowcase from '@/components/marketing/FeatureShowcase.vue';
import FinalCta from '@/components/marketing/FinalCta.vue';
import HeroSection from '@/components/marketing/HeroSection.vue';
import IntegrationsGrid from '@/components/marketing/IntegrationsGrid.vue';
import AdminDashboardMock from '@/components/marketing/jamaica/AdminDashboardMock.vue';
import PackageJourney from '@/components/marketing/jamaica/PackageJourney.vue';
import LogoMarquee from '@/components/marketing/LogoMarquee.vue';
import MarketingFooter from '@/components/marketing/MarketingFooter.vue';
import MarketingNav from '@/components/marketing/MarketingNav.vue';
import PeopleSection from '@/components/marketing/PeopleSection.vue';
import PricingSection from '@/components/marketing/PricingSection.vue';
import ProductTeaser from '@/components/marketing/ProductTeaser.vue';
import StackingFeatureCards from '@/components/marketing/StackingFeatureCards.vue';
import StatsBanner from '@/components/marketing/StatsBanner.vue';
import TestimonialSwitcher from '@/components/marketing/TestimonialSwitcher.vue';
import TrustBar from '@/components/marketing/TrustBar.vue';
import { useScrollReveal } from '@/composables/useScrollReveal';

const props = defineProps<{
    pricing: {
        monthly: number;
        setup: number;
        currency: string;
        founding_setup?: number | null;
    };
    salesWhatsapp?: string | null;
    liveDemoUrl?: string | null;
}>();

const salesWhatsappHref = props.salesWhatsapp
    ? `https://wa.me/${props.salesWhatsapp}?text=${encodeURIComponent("Hi Shane, I'd like to know more about CourierOS.")}`
    : null;

useScrollReveal();
</script>

<template>
    <!-- The brand name is appended automatically by the title callback in
         app.ts, so leaving "CourierOS" here duplicated it in the tab. -->
    <Head title="Run Your Courier Business Like a Platform">
        <meta
            name="description"
            content="CourierOS is the branded website and back-office software behind a modern courier business: pre-alerts, package tracking, billing and customer updates, all in one place."
        />
        <meta property="og:type" content="website" />
        <meta
            property="og:title"
            content="CourierOS — Run Your Courier Business Like a Platform"
        />
        <meta
            property="og:description"
            content="Pre-alerts, package tracking, billing and customer updates — on your own branded site. Launch in minutes, not months."
        />
        <meta
            property="og:image"
            content="/images/marketing/og/courieros-og.png"
        />
        <meta name="twitter:card" content="summary_large_image" />
        <meta
            name="twitter:title"
            content="CourierOS — Run Your Courier Business Like a Platform"
        />
        <meta
            name="twitter:description"
            content="Pre-alerts, package tracking, billing and customer updates — on your own branded site. Launch in minutes, not months."
        />
        <meta
            name="twitter:image"
            content="/images/marketing/og/courieros-og.png"
        />
    </Head>

    <div class="bg-marketing-cream font-marketing text-marketing-ink">
        <AnnouncementBar
            text="Now welcoming Caribbean courier businesses"
            href="/signup"
        />
        <MarketingNav />

        <HeroSection :pricing="pricing" :live-demo-url="liveDemoUrl" />

        <TrustBar />

        <LogoMarquee :logos="[]" />

        <TestimonialSwitcher :testimonials="[]" />

        <IntegrationsGrid />

        <ProductTeaser :live-demo-url="liveDemoUrl" />

        <PeopleSection :whatsapp-href="salesWhatsappHref" />

        <StackingFeatureCards
            eyebrow="Capabilities"
            title="What CourierOS can do for you"
            body="Everything you need to run the warehouse, in one place."
        >
            <FeatureShowcase
                eyebrow="Stay on top of every parcel"
                title="Real-time package &amp; pre-alert tracking"
                body="Every pre-alert and package status update lands in one dashboard, so you always know what's moving through your warehouse."
                :bullets="[
                    'Live status timeline',
                    'Instant pre-alert matching',
                ]"
            >
                <template #visual>
                    <PackageJourney />
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
                    <AdminDashboardMock />
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
                                color: '#FF7A00',
                                note: '65% less',
                            },
                        ]"
                    />
                </template>
            </FeatureShowcase>
        </StackingFeatureCards>

        <StatsBanner />

        <PricingSection :pricing="pricing" />

        <FeatureGrid />

        <FaqAccordion />

        <FinalCta />

        <MarketingFooter />
    </div>
</template>
