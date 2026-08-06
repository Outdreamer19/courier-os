<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Crown, Pencil, Plus, ShieldCheck, UserCog, UserRound, Users } from 'lucide-vue-next';
import { computed } from 'vue';
import {
    DataTable,
    DataTableBody,
    DataTableCell,
    DataTableFooter,
    DataTableHeader,
    DataTableHeaderCell,
    DataTableRow,
} from '@/components/data-table';
import EmptyState from '@/components/EmptyState.vue';
import InitialsAvatar from '@/components/InitialsAvatar.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { dashboard as adminDashboard } from '@/routes/admin';
import { create, edit, index } from '@/routes/admin/admin-users';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Admin', href: adminDashboard() },
            { title: 'Admin users', href: index() },
        ],
    },
});

type AdminRow = {
    id: number;
    name: string;
    email: string;
    role: string;
    role_label: string;
    status: string;
    created_at: string | null;
    is_self: boolean;
    can_edit: boolean;
};

const props = defineProps<{
    admins: {
        data: AdminRow[];
        links: Array<{ url: string | null; label: string; active: boolean }>;
        from: number | null;
        to: number | null;
        total: number;
    };
    roles: Record<string, string>;
    stats: {
        total: number;
        active: number;
        suspended: number;
        by_role: Array<{ role: string; label: string; count: number }>;
    };
}>();

/**
 * Role is the thing an admin scans this table for, so each one gets its own
 * icon and tint rather than sharing a single neutral badge. Owner reads as the
 * most privileged (navy, the brand's primary), staff as the least (muted).
 */
const ROLE_STYLES: Record<
    string,
    { icon: typeof Crown; badge: string; tile: string }
> = {
    owner: {
        icon: Crown,
        badge: 'border-brand-ink/25 bg-brand-ink/10 text-brand-ink dark:text-brand-cream',
        tile: 'bg-brand-ink/10 text-brand-ink',
    },
    admin: {
        icon: ShieldCheck,
        badge: 'border-violet-500/25 bg-violet-500/10 text-violet-700 dark:text-violet-300',
        tile: 'bg-violet-500/10 text-violet-600 dark:text-violet-300',
    },
    staff: {
        icon: UserRound,
        badge: 'border-border bg-muted text-muted-foreground',
        tile: 'bg-muted text-muted-foreground',
    },
};

const roleStyle = (role: string) => ROLE_STYLES[role] ?? ROLE_STYLES.staff!;

const joinedLabel = (iso: string | null) => {
    if (!iso) {
        return '—';
    }

    return new Date(iso).toLocaleDateString(undefined, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const summaryCards = computed(() => [
    {
        key: 'total',
        label: 'Team members',
        value: props.stats.total,
        icon: Users,
        tile: 'bg-brand-ink/10 text-brand-ink',
    },
    {
        key: 'active',
        label: 'Active',
        value: props.stats.active,
        icon: ShieldCheck,
        tile: 'bg-brand-green/15 text-brand-green',
    },
    {
        key: 'suspended',
        label: 'Suspended',
        value: props.stats.suspended,
        icon: UserCog,
        tile: 'bg-muted text-muted-foreground',
    },
]);
</script>

<template>
    <Head title="Admin · Admin users" />

    <div class="flex flex-col gap-6 p-4 lg:p-6">
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">Admin users</h1>
                <p class="text-sm text-muted-foreground">
                    Manage who can access the admin portal and what they can do.
                </p>
            </div>
            <Button as-child class="bg-brand-ink text-white hover:bg-brand-ink-soft">
                <Link :href="create()">
                    <Plus class="size-4" />
                    Add admin user
                </Link>
            </Button>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <Card v-for="card in summaryCards" :key="card.key">
                <CardContent class="flex items-center gap-3 pt-6">
                    <div
                        :class="[
                            'flex size-10 items-center justify-center rounded-lg',
                            card.tile,
                        ]"
                    >
                        <component :is="card.icon" class="size-5" />
                    </div>
                    <div>
                        <p class="text-2xl font-semibold tabular-nums">
                            {{ card.value }}
                        </p>
                        <p class="text-xs text-muted-foreground">{{ card.label }}</p>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Role breakdown: a single strip rather than three more stat cards, so
             the counts read as one distribution instead of unrelated numbers. -->
        <Card v-if="stats.total">
            <CardContent class="flex flex-wrap items-center gap-x-8 gap-y-4 pt-6">
                <p class="text-sm font-medium">Roles</p>
                <div
                    v-for="entry in stats.by_role"
                    :key="entry.role"
                    class="flex items-center gap-2.5"
                >
                    <div
                        :class="[
                            'flex size-8 items-center justify-center rounded-lg',
                            roleStyle(entry.role).tile,
                        ]"
                    >
                        <component :is="roleStyle(entry.role).icon" class="size-4" />
                    </div>
                    <div class="leading-tight">
                        <p class="text-sm font-semibold tabular-nums">
                            {{ entry.count }}
                        </p>
                        <p class="text-xs text-muted-foreground">
                            {{ entry.label }}
                        </p>
                    </div>
                </div>
            </CardContent>
        </Card>

        <Card v-if="admins.data.length" class="gap-0 overflow-hidden py-0">
            <CardContent class="px-0">
                <DataTable min-width="760px">
                    <DataTableHeader>
                        <DataTableHeaderCell width="34%">
                            Team member
                        </DataTableHeaderCell>
                        <DataTableHeaderCell width="24%">
                            Role
                        </DataTableHeaderCell>
                        <DataTableHeaderCell width="14%">
                            Status
                        </DataTableHeaderCell>
                        <DataTableHeaderCell width="18%">
                            Joined
                        </DataTableHeaderCell>
                        <DataTableHeaderCell width="10%">
                            <span class="sr-only">Actions</span>
                        </DataTableHeaderCell>
                    </DataTableHeader>
                    <DataTableBody>
                        <DataTableRow v-for="admin in admins.data" :key="admin.id">
                            <DataTableCell>
                                <div class="flex items-center gap-3">
                                    <InitialsAvatar :name="admin.name" />
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <span class="truncate font-medium">
                                                {{ admin.name }}
                                            </span>
                                            <Badge
                                                v-if="admin.is_self"
                                                variant="outline"
                                                class="shrink-0 px-1.5 py-0 text-[10px] font-semibold tracking-wide text-muted-foreground uppercase"
                                            >
                                                You
                                            </Badge>
                                        </div>
                                        <p
                                            class="truncate text-xs text-muted-foreground"
                                        >
                                            {{ admin.email }}
                                        </p>
                                    </div>
                                </div>
                            </DataTableCell>
                            <DataTableCell>
                                <Badge
                                    variant="outline"
                                    :class="[
                                        'gap-1.5 py-0.5 pr-2.5 pl-2 text-xs font-semibold whitespace-nowrap',
                                        roleStyle(admin.role).badge,
                                    ]"
                                >
                                    <component
                                        :is="roleStyle(admin.role).icon"
                                        class="size-3.5 shrink-0"
                                    />
                                    {{ admin.role_label }}
                                </Badge>
                            </DataTableCell>
                            <DataTableCell>
                                <StatusBadge
                                    :status="admin.status"
                                    :label="admin.status"
                                    class="capitalize"
                                />
                            </DataTableCell>
                            <DataTableCell
                                class="text-sm text-muted-foreground tabular-nums"
                            >
                                {{ joinedLabel(admin.created_at) }}
                            </DataTableCell>
                            <DataTableCell align="right">
                                <Button
                                    v-if="admin.can_edit"
                                    as-child
                                    variant="outline"
                                    size="icon-sm"
                                    class="text-muted-foreground opacity-70 transition-all group-hover:opacity-100 hover:border-brand-ink/50 hover:bg-brand-ink/10 hover:text-brand-ink dark:hover:bg-brand-ink/25 dark:hover:text-brand-cream"
                                >
                                    <Link
                                        :href="edit(admin.id)"
                                        :title="`Edit ${admin.name}`"
                                    >
                                        <Pencil class="size-4" />
                                        <span class="sr-only">
                                            Edit {{ admin.name }}
                                        </span>
                                    </Link>
                                </Button>
                            </DataTableCell>
                        </DataTableRow>
                    </DataTableBody>
                </DataTable>
            </CardContent>

            <DataTableFooter
                :links="admins.links"
                :from="admins.from"
                :to="admins.to"
                :total="admins.total"
                noun="admin users"
            />
        </Card>

        <Card v-else>
            <CardContent>
                <EmptyState
                    :icon="Users"
                    title="No admin users yet"
                    description="Invite a colleague so they can help manage packages, customers and rates."
                >
                    <Button
                        as-child
                        class="bg-brand-ink text-white hover:bg-brand-ink-soft"
                    >
                        <Link :href="create()">Add admin user</Link>
                    </Button>
                </EmptyState>
            </CardContent>
        </Card>
    </div>
</template>
