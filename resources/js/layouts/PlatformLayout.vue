<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    Building2,
    ChevronUp,
    Home,
    LifeBuoy,
    LogOut,
    Menu,
    X,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import InitialsAvatar from '@/components/InitialsAvatar.vue';
import { Toaster } from '@/components/ui/sonner';
import { logout } from '@/routes';

type AttentionItem = {
    type: string;
    severity: string;
    title: string;
    detail: string;
};

withDefaults(
    defineProps<{
        current: 'overview' | 'tenants' | 'support';
        attention?: AttentionItem[];
    }>(),
    { attention: () => [] },
);

const page = usePage();
const user = computed(() => page.props.auth?.user ?? null);
const unread = computed(
    () => Number((page.props as { platform_support_unread?: number }).platform_support_unread ?? 0),
);
const mobileOpen = ref(false);

const nav = [
    { key: 'overview' as const, label: 'Home', href: '/platform', icon: Home },
    {
        key: 'tenants' as const,
        label: 'Tenants',
        href: '/platform/tenants',
        icon: Building2,
    },
    {
        key: 'support' as const,
        label: 'Support',
        href: '/platform/support',
        icon: LifeBuoy,
    },
];

const navClass = (active: boolean) =>
    [
        'flex w-full items-center gap-3 rounded-lg px-2 py-2.5 text-left text-base/6 font-medium sm:py-2 sm:text-sm/5',
        active
            ? 'bg-zinc-950/5 text-zinc-950'
            : 'text-zinc-950 hover:bg-zinc-950/5',
    ].join(' ');
</script>

<template>
    <div
        class="relative isolate flex min-h-svh w-full bg-white text-zinc-950 antialiased max-lg:flex-col lg:bg-zinc-100"
    >
        <!-- Desktop sidebar (light, sits on zinc-100) -->
        <div class="fixed inset-y-0 left-0 w-64 max-lg:hidden">
            <nav class="flex h-full min-h-0 flex-col">
                <div class="flex flex-1 flex-col overflow-y-auto p-4">
                    <div class="flex items-center gap-2.5 px-2 py-1.5">
                        <div
                            class="flex size-8 items-center justify-center rounded-lg bg-zinc-950 text-sm font-bold text-white"
                        >
                            C
                        </div>
                        <div class="min-w-0">
                            <p
                                class="truncate text-sm/5 font-semibold text-zinc-950"
                            >
                                CourierOS
                            </p>
                            <p class="truncate text-xs/5 text-zinc-500">
                                Platform
                            </p>
                        </div>
                    </div>

                    <div class="mt-6 flex flex-col gap-0.5">
                        <Link
                            v-for="item in nav"
                            :key="item.key"
                            :href="item.href"
                            :class="navClass(current === item.key)"
                        >
                            <component
                                :is="item.icon"
                                class="size-5 shrink-0 text-zinc-500"
                                :stroke-width="1.75"
                            />
                            <span class="flex-1">{{ item.label }}</span>
                            <span
                                v-if="item.key === 'support' && unread > 0"
                                class="rounded-full bg-zinc-950 px-1.5 py-0.5 text-[10px] font-semibold text-white"
                            >
                                {{ unread > 99 ? '99+' : unread }}
                            </span>
                        </Link>
                    </div>

                    <div
                        v-if="attention.length"
                        class="mt-8 flex flex-col gap-1"
                    >
                        <h3
                            class="px-2 text-xs/6 font-medium text-zinc-500"
                        >
                            Needs attention
                        </h3>
                        <Link
                            v-for="(item, index) in attention.slice(0, 5)"
                            :key="index"
                            href="/platform/tenants"
                            class="rounded-lg px-2 py-1.5 text-sm/5 text-zinc-700 hover:bg-zinc-950/5"
                        >
                            <span
                                class="mr-2 inline-block size-1.5 rounded-full align-middle"
                                :class="
                                    item.severity === 'danger'
                                        ? 'bg-rose-500'
                                        : 'bg-amber-500'
                                "
                            />
                            {{ item.title }}
                        </Link>
                    </div>
                </div>

                <div class="flex flex-col border-t border-zinc-950/5 p-4">
                    <div
                        v-if="user"
                        class="flex w-full items-center gap-3 rounded-lg px-2 py-2.5 sm:py-2"
                    >
                        <InitialsAvatar :name="user.name" class="size-8" />
                        <div class="min-w-0 flex-1">
                            <p
                                class="truncate text-sm/5 font-medium text-zinc-950"
                            >
                                {{ user.name }}
                            </p>
                            <p class="truncate text-xs/5 text-zinc-500">
                                {{ user.email }}
                            </p>
                        </div>
                        <Link
                            :href="logout()"
                            as="button"
                            method="post"
                            class="rounded-md p-1.5 text-zinc-500 hover:bg-zinc-950/5 hover:text-zinc-950"
                            aria-label="Sign out"
                            title="Sign out"
                        >
                            <LogOut class="size-4" />
                        </Link>
                    </div>
                    <div
                        v-else
                        class="flex items-center gap-2 px-2 py-2 text-xs text-zinc-500"
                    >
                        <ChevronUp class="size-4" />
                        Not signed in
                    </div>
                </div>
            </nav>
        </div>

        <!-- Mobile top bar -->
        <div
            class="sticky top-0 z-40 flex items-center justify-between border-b border-zinc-950/5 bg-white px-4 py-3 lg:hidden"
        >
            <div class="flex items-center gap-2.5">
                <div
                    class="flex size-8 items-center justify-center rounded-lg bg-zinc-950 text-sm font-bold text-white"
                >
                    C
                </div>
                <span class="text-sm font-semibold text-zinc-950"
                    >CourierOS</span
                >
            </div>
            <button
                type="button"
                class="rounded-lg p-2 text-zinc-500 hover:bg-zinc-950/5 hover:text-zinc-950"
                :aria-label="mobileOpen ? 'Close menu' : 'Open menu'"
                @click="mobileOpen = !mobileOpen"
            >
                <X v-if="mobileOpen" class="size-5" />
                <Menu v-else class="size-5" />
            </button>
        </div>

        <!-- Mobile drawer -->
        <div
            v-if="mobileOpen"
            class="fixed inset-0 z-40 lg:hidden"
        >
            <div
                class="fixed inset-0 bg-black/30"
                @click="mobileOpen = false"
            />
            <div
                class="fixed inset-y-0 left-0 w-full max-w-80 bg-white p-4 shadow-lg"
            >
                <div class="mb-4 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div
                            class="flex size-8 items-center justify-center rounded-lg bg-zinc-950 text-sm font-bold text-white"
                        >
                            C
                        </div>
                        <span class="text-sm font-semibold">CourierOS</span>
                    </div>
                    <button
                        type="button"
                        class="rounded-lg p-2 text-zinc-500 hover:bg-zinc-950/5"
                        aria-label="Close menu"
                        @click="mobileOpen = false"
                    >
                        <X class="size-5" />
                    </button>
                </div>
                <div class="flex flex-col gap-0.5">
                    <Link
                        v-for="item in nav"
                        :key="item.key"
                        :href="item.href"
                        :class="navClass(current === item.key)"
                        @click="mobileOpen = false"
                    >
                        <component
                            :is="item.icon"
                            class="size-5 shrink-0 text-zinc-500"
                            :stroke-width="1.75"
                        />
                        <span class="flex-1">{{ item.label }}</span>
                        <span
                            v-if="item.key === 'support' && unread > 0"
                            class="rounded-full bg-zinc-950 px-1.5 py-0.5 text-[10px] font-semibold text-white"
                        >
                            {{ unread > 99 ? '99+' : unread }}
                        </span>
                    </Link>
                </div>
                <div
                    v-if="user"
                    class="mt-8 flex items-center gap-3 border-t border-zinc-950/5 pt-4"
                >
                    <InitialsAvatar :name="user.name" class="size-8" />
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium">
                            {{ user.name }}
                        </p>
                        <p class="truncate text-xs text-zinc-500">
                            {{ user.email }}
                        </p>
                    </div>
                    <Link
                        :href="logout()"
                        as="button"
                        method="post"
                        class="rounded-md p-1.5 text-zinc-500 hover:bg-zinc-950/5"
                        aria-label="Sign out"
                    >
                        <LogOut class="size-4" />
                    </Link>
                </div>
            </div>
        </div>

        <!-- Main: white rounded panel on zinc-100 -->
        <main
            class="flex flex-1 flex-col pb-2 lg:min-w-0 lg:pt-2 lg:pr-2 lg:pl-64"
        >
            <div
                class="grow p-6 lg:rounded-lg lg:bg-white lg:p-10 lg:shadow-xs lg:ring-1 lg:ring-zinc-950/5"
            >
                <slot />
            </div>
        </main>

        <Toaster richColors position="top-right" />
    </div>
</template>
