<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { dashboard as adminDashboard } from '@/routes/admin';
import { index, update } from '@/routes/admin/contact-messages';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Admin', href: adminDashboard() },
            { title: 'Contact inbox', href: index() },
            { title: 'Message', href: '#' },
        ],
    },
});

defineProps<{
    message: {
        id: number;
        name: string;
        email: string;
        phone: string | null;
        subject: string;
        body: string;
        status: string;
        admin_notes: string | null;
    };
    statuses: Record<string, string>;
}>();
</script>

<template>
    <Head :title="`Contact · ${message.subject}`" />

    <div class="flex flex-col gap-6 p-4 lg:p-6">
        <Card>
            <CardHeader>
                <CardTitle>{{ message.subject }}</CardTitle>
                <p class="text-sm text-muted-foreground">
                    {{ message.name }} ·
                    <a :href="`mailto:${message.email}`" class="underline">
                        {{ message.email }}
                    </a>
                    <span v-if="message.phone"> · {{ message.phone }}</span>
                </p>
            </CardHeader>
            <CardContent class="whitespace-pre-wrap text-sm leading-relaxed">
                {{ message.body }}
            </CardContent>
        </Card>

        <Card>
            <CardHeader>
                <CardTitle>Triage</CardTitle>
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
                            class="h-9 w-full rounded-md border px-3 text-sm"
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
                            class="w-full rounded-md border px-3 py-2 text-sm"
                            :default-value="message.admin_notes ?? ''"
                        />
                    </div>
                    <Button
                        type="submit"
                        class="bg-brand-gold text-brand-ink hover:bg-brand-gold-soft"
                        :disabled="processing"
                    >
                        Save
                    </Button>
                </Form>
            </CardContent>
        </Card>

        <Button as-child variant="outline" class="w-fit">
            <Link :href="index()">Back to inbox</Link>
        </Button>
    </div>
</template>
