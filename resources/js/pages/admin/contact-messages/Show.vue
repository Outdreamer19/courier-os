<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import {
    ArrowLeft,
    CheckCircle2,
    Mail,
    Trash2,
} from 'lucide-vue-next';
import InputError from '@/components/InputError.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { dashboard as adminDashboard } from '@/routes/admin';
import { destroy, index, update } from '@/routes/admin/contact-messages';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Admin', href: adminDashboard() },
            { title: 'Contact inbox', href: index() },
            { title: 'Message', href: '#' },
        ],
    },
});

const props = defineProps<{
    message: {
        id: number;
        name: string;
        email: string;
        phone: string | null;
        subject: string;
        body: string;
        status: string;
        status_label: string;
        admin_notes: string | null;
        created_at: string | null;
        handled_at: string | null;
    };
    statuses: Record<string, string>;
}>();

const replyMailto = () => {
    const subject = encodeURIComponent(`Re: ${props.message.subject}`);
    const body = encodeURIComponent(
        `Hi ${props.message.name},\n\nThank you for contacting TODAY Shipping.\n\n`,
    );

    return `mailto:${props.message.email}?subject=${subject}&body=${body}`;
};

const formatDate = (value: string | null) => {
    if (!value) {
        return '—';
    }

    return new Date(value).toLocaleString();
};
</script>

<template>
    <Head :title="`Contact · ${message.subject}`" />

    <div class="flex flex-col gap-6 p-4 lg:p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="space-y-2">
                <Button as-child variant="ghost" size="sm" class="w-fit -ml-2">
                    <Link :href="index()">
                        <ArrowLeft class="size-4" />
                        Back to inbox
                    </Link>
                </Button>
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight">
                        {{ message.subject }}
                    </h1>
                    <div class="mt-2 flex flex-wrap items-center gap-2">
                        <StatusBadge
                            :status="message.status"
                            :label="message.status_label"
                        />
                        <span class="text-sm text-muted-foreground">
                            Received {{ formatDate(message.created_at) }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                <Button as-child variant="outline">
                    <a :href="replyMailto()">
                        <Mail class="size-4" />
                        Reply via email
                    </a>
                </Button>
                <Form
                    v-if="message.status !== 'resolved'"
                    v-bind="update.form(message.id)"
                    :options="{ preserveScroll: true }"
                >
                    <input type="hidden" name="status" value="resolved" />
                    <input
                        type="hidden"
                        name="admin_notes"
                        :value="message.admin_notes ?? ''"
                    />
                    <Button
                        type="submit"
                        variant="outline"
                        class="border-brand-green/40 text-brand-green hover:bg-brand-green/10"
                    >
                        <CheckCircle2 class="size-4" />
                        Mark resolved
                    </Button>
                </Form>
                <Dialog>
                    <DialogTrigger as-child>
                        <Button variant="destructive">
                            <Trash2 class="size-4" />
                            Delete
                        </Button>
                    </DialogTrigger>
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>Delete this message?</DialogTitle>
                            <DialogDescription>
                                This permanently removes the message from
                                {{ message.name }}. This cannot be undone.
                            </DialogDescription>
                        </DialogHeader>
                        <DialogFooter>
                            <DialogClose as-child>
                                <Button variant="outline">Cancel</Button>
                            </DialogClose>
                            <Form v-bind="destroy.form(message.id)">
                                <Button type="submit" variant="destructive">
                                    Delete message
                                </Button>
                            </Form>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <Card class="lg:col-span-2">
                <CardHeader>
                    <CardTitle>Message</CardTitle>
                    <CardDescription>
                        {{ message.name }} ·
                        <a
                            :href="`mailto:${message.email}`"
                            class="underline"
                        >
                            {{ message.email }}
                        </a>
                        <span v-if="message.phone">
                            · {{ message.phone }}
                        </span>
                    </CardDescription>
                </CardHeader>
                <CardContent
                    class="whitespace-pre-wrap rounded-lg border bg-muted/30 p-4 text-sm leading-relaxed"
                >
                    {{ message.body }}
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>Details</CardTitle>
                </CardHeader>
                <CardContent>
                    <dl class="space-y-3 text-sm">
                        <div>
                            <dt class="text-muted-foreground">Status</dt>
                            <dd class="mt-0.5 font-medium">
                                {{ message.status_label }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-muted-foreground">Last handled</dt>
                            <dd class="mt-0.5 font-medium">
                                {{ formatDate(message.handled_at) }}
                            </dd>
                        </div>
                    </dl>
                </CardContent>
            </Card>
        </div>

        <Card>
            <CardHeader>
                <CardTitle>Triage</CardTitle>
                <CardDescription>
                    Update status and keep internal notes for your team.
                </CardDescription>
            </CardHeader>
            <CardContent>
                <Form
                    v-bind="update.form(message.id)"
                    class="space-y-4"
                    v-slot="{ errors, processing }"
                >
                    <div class="grid gap-2">
                        <Label for="status">Status</Label>
                        <select
                            id="status"
                            name="status"
                            class="h-9 w-full rounded-md border border-input bg-background px-3 text-sm"
                            :default-value="message.status"
                        >
                            <option
                                v-for="(label, value) in statuses"
                                :key="value"
                                :value="value"
                            >
                                {{ label }}
                            </option>
                        </select>
                        <InputError :message="errors.status" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="admin_notes">Internal notes</Label>
                        <textarea
                            id="admin_notes"
                            name="admin_notes"
                            rows="4"
                            class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                            :default-value="message.admin_notes ?? ''"
                        />
                        <InputError :message="errors.admin_notes" />
                    </div>
                    <Button
                        type="submit"
                        class="bg-brand-gold text-white hover:bg-brand-gold-soft"
                        :disabled="processing"
                    >
                        Save changes
                    </Button>
                </Form>
            </CardContent>
        </Card>
    </div>
</template>
