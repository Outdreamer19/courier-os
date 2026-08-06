<script setup lang="ts">
/**
 * The hero's background. Four stacked layers, cheapest first:
 *   1. a warm base wash so the section never reads as flat cream
 *   2. drifting colour blobs in the marketing palette
 *   3. a dot grid, radially masked so it fades out before the edges
 *   4. an animated freight route — the product's actual subject matter
 *
 * Everything is decorative and pointer-events-none; all motion is disabled
 * under prefers-reduced-motion via the utilities in app.css.
 */
</script>

<template>
    <div class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
        <!-- 1. Base wash -->
        <div
            class="absolute inset-0"
            style="
                background:
                    radial-gradient(
                        1200px 620px at 50% -12%,
                        rgba(252, 213, 25, 0.28),
                        transparent 62%
                    ),
                    radial-gradient(
                        900px 520px at 8% 8%,
                        rgba(250, 143, 31, 0.18),
                        transparent 60%
                    ),
                    radial-gradient(
                        900px 560px at 96% 14%,
                        rgba(198, 223, 250, 0.5),
                        transparent 62%
                    ),
                    linear-gradient(
                        180deg,
                        #fffbf5 0%,
                        #fffaf0 45%,
                        #fffbf5 100%
                    );
            "
        />

        <!-- 2. Drifting colour blobs -->
        <div
            class="hero-blob absolute -top-28 -left-24 h-[30rem] w-[30rem] rounded-full opacity-60 blur-3xl"
            style="
                background: radial-gradient(
                    circle at 30% 30%,
                    rgba(250, 143, 31, 0.55),
                    transparent 70%
                );
            "
        />
        <div
            class="hero-blob hero-blob-slow absolute -top-16 right-[-6rem] h-[34rem] w-[34rem] rounded-full opacity-55 blur-3xl"
            style="
                background: radial-gradient(
                    circle at 60% 40%,
                    rgba(214, 205, 240, 0.75),
                    transparent 70%
                );
            "
        />
        <div
            class="hero-blob hero-blob-slower absolute top-40 left-1/2 h-[28rem] w-[38rem] -translate-x-1/2 rounded-full opacity-45 blur-3xl"
            style="
                background: radial-gradient(
                    circle at 50% 50%,
                    rgba(252, 213, 25, 0.6),
                    transparent 70%
                );
            "
        />

        <!-- 3. Dot grid, masked to a soft vignette -->
        <div
            class="absolute inset-0 opacity-[0.35]"
            style="
                background-image: radial-gradient(
                    rgba(33, 13, 2, 0.16) 1px,
                    transparent 1px
                );
                background-size: 26px 26px;
                mask-image: radial-gradient(
                    ellipse 78% 62% at 50% 34%,
                    #000 35%,
                    transparent 78%
                );
                -webkit-mask-image: radial-gradient(
                    ellipse 78% 62% at 50% 34%,
                    #000 35%,
                    transparent 78%
                );
            "
        />

        <!-- 4. Freight route: parcels moving between warehouse and island -->
        <!-- Kept low-contrast and routed around the headline block: the arcs
             should read as texture in peripheral vision, never as something
             competing with the copy for attention. -->
        <svg
            class="absolute inset-x-0 top-0 h-full w-full opacity-[0.32]"
            viewBox="0 0 1440 720"
            preserveAspectRatio="xMidYMid slice"
            fill="none"
        >
            <defs>
                <linearGradient id="hero-route-a" x1="0" y1="0" x2="1" y2="0">
                    <stop offset="0%" stop-color="#ff7a00" stop-opacity="0" />
                    <stop offset="50%" stop-color="#ff7a00" stop-opacity="0.75" />
                    <stop offset="100%" stop-color="#ff7a00" stop-opacity="0" />
                </linearGradient>
                <linearGradient id="hero-route-b" x1="0" y1="0" x2="1" y2="0">
                    <stop offset="0%" stop-color="#aa8322" stop-opacity="0" />
                    <stop offset="50%" stop-color="#aa8322" stop-opacity="0.6" />
                    <stop offset="100%" stop-color="#aa8322" stop-opacity="0" />
                </linearGradient>
            </defs>

            <!-- Left flank, sweeping down and away from the headline -->
            <path
                class="hero-route-line"
                d="M -60 210 C 140 300, 200 470, 150 690"
                stroke="url(#hero-route-a)"
                stroke-width="2"
                stroke-linecap="round"
            />
            <!-- Right flank -->
            <path
                class="hero-route-line"
                style="animation-duration: 19s; animation-direction: reverse"
                d="M 1500 190 C 1300 290, 1250 460, 1310 700"
                stroke="url(#hero-route-b)"
                stroke-width="2"
                stroke-linecap="round"
            />
            <!-- Wide arc across the very top, above the copy -->
            <path
                class="hero-route-line"
                style="animation-duration: 24s"
                d="M -60 96 C 340 18, 1100 18, 1500 104"
                stroke="url(#hero-route-a)"
                stroke-width="2"
                stroke-linecap="round"
            />

            <!-- Waypoints -->
            <g fill="#ff7a00">
                <circle class="hero-pulse" cx="126" cy="330" r="4" />
                <circle
                    class="hero-pulse"
                    style="animation-delay: -1.1s"
                    cx="720"
                    cy="34"
                    r="4"
                />
            </g>
            <g fill="#aa8322">
                <circle
                    class="hero-pulse"
                    style="animation-delay: -0.6s"
                    cx="1298"
                    cy="352"
                    r="3.5"
                />
                <circle
                    class="hero-pulse"
                    style="animation-delay: -1.8s"
                    cx="168"
                    cy="600"
                    r="3.5"
                />
            </g>
        </svg>

        <!-- Fade the whole thing into the section below -->
        <div
            class="absolute inset-x-0 bottom-0 h-40 bg-gradient-to-b from-transparent to-marketing-cream"
        />
    </div>
</template>
